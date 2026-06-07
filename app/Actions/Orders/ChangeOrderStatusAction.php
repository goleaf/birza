<?php

namespace App\Actions\Orders;

use App\Actions\Notifications\SendMarketplaceNotificationAction;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Users\Seller;
use App\Notifications\Marketplace\OrderStatusChangedNotification;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeOrderStatusAction
{
    public function __construct(
        private readonly SendMarketplaceNotificationAction $sendNotification,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handle(
        Order $order,
        OrderStatus $nextStatus,
        ?Authenticatable $actor,
        ?string $reason = null,
        ?string $note = null,
    ): OrderStatusHistory {
        $role = OrderStatusActorRole::fromActor($actor);
        $reason = $this->cleanText($reason);
        $note = $this->cleanText($note);

        $history = DB::transaction(function () use ($order, $nextStatus, $actor, $role, $reason, $note): OrderStatusHistory {
            $lockedOrder = Order::query()
                ->with(['items.product', 'items.seller', 'buyer'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            $currentStatus = $lockedOrder->lifecycleStatus();

            $this->authorize($lockedOrder, $currentStatus, $nextStatus, $actor, $role);
            $this->validateReason($role, $reason);
            $this->applySideEffects($lockedOrder, $currentStatus, $nextStatus, $note);

            $newPaymentStatus = $this->paymentStatusFor($nextStatus);
            $oldPaymentStatus = OrderPaymentStatus::fromValue($lockedOrder->getOriginal('payment_status'));

            Order::allowStatusMutation(function () use ($lockedOrder, $nextStatus, $newPaymentStatus): void {
                $lockedOrder->forceFill([
                    'status' => $nextStatus,
                    'payment_status' => $newPaymentStatus,
                ])->save();
            });

            $history = $lockedOrder->statusHistory()->create([
                'old_status' => $currentStatus,
                'new_status' => $nextStatus,
                'changed_by_user_id' => $this->actorId($actor),
                'changed_by_role' => $role,
                'reason' => $reason,
                'note' => $note,
            ]);

            $this->auditLogService->log(
                actor: $actor,
                action: 'order.status_changed',
                auditable: $lockedOrder,
                oldValues: [
                    'status' => $currentStatus->value,
                    'payment_status' => $oldPaymentStatus->value,
                ],
                newValues: [
                    'status' => $nextStatus->value,
                    'payment_status' => $newPaymentStatus->value,
                ],
                metadata: [
                    'source' => 'order_status_action',
                    'order_total' => $lockedOrder->order_total,
                    'buyer_id' => $lockedOrder->buyer_id,
                    'seller_ids' => $lockedOrder->items->pluck('seller_id')->unique()->values()->all(),
                    'history_id' => $history->id,
                    'note' => $note,
                ],
                reason: $reason,
            );

            $this->logSpecificOrderStatusAction($lockedOrder, $currentStatus, $nextStatus, $actor, $reason, $note);

            return $history;
        });

        $history->load(['order.buyer', 'order.items.seller']);
        $this->sendNotifications($history, $actor);

        return $history;
    }

    private function authorize(
        Order $order,
        OrderStatus $currentStatus,
        OrderStatus $nextStatus,
        ?Authenticatable $actor,
        OrderStatusActorRole $role,
    ): void {
        if (! $currentStatus->canTransitionTo($nextStatus)) {
            throw ValidationException::withMessages([
                'status' => __('orders.status.messages.transition_not_allowed', [
                    'from' => $currentStatus->label(),
                    'to' => $nextStatus->label(),
                ]),
            ]);
        }

        if (! $order->isManageableBy($actor, $role) || ! $nextStatus->canBeChangedBy($role)) {
            throw new AuthorizationException(__('orders.messages.unauthorized_update'));
        }
    }

    private function validateReason(OrderStatusActorRole $role, ?string $reason): void
    {
        if ($role !== OrderStatusActorRole::Admin || filled($reason)) {
            return;
        }

        throw ValidationException::withMessages([
            'reason' => __('orders.status.messages.reason_required'),
        ]);
    }

    private function applySideEffects(Order $order, OrderStatus $oldStatus, OrderStatus $newStatus, ?string $note): void
    {
        if ($oldStatus === OrderStatus::Pending && $newStatus === OrderStatus::Accepted) {
            $this->creditSellers($order, $note);
        }

        if ($this->shouldRestoreStock($oldStatus, $newStatus)) {
            $this->restoreStock($order);
        }

        if ($oldStatus->isRevenueRecognized() && in_array($newStatus, [OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
            $this->debitSellers($order, $note);
        }
    }

    private function paymentStatusFor(OrderStatus $status): OrderPaymentStatus
    {
        return match ($status) {
            OrderStatus::Pending => OrderPaymentStatus::Pending,
            OrderStatus::Accepted,
            OrderStatus::Processing,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
            OrderStatus::Completed,
            OrderStatus::Disputed => OrderPaymentStatus::Paid,
            OrderStatus::Rejected => OrderPaymentStatus::Refunded,
            OrderStatus::Cancelled => OrderPaymentStatus::Cancelled,
            OrderStatus::Refunded => OrderPaymentStatus::Refunded,
        };
    }

    private function shouldRestoreStock(OrderStatus $oldStatus, OrderStatus $newStatus): bool
    {
        return in_array($newStatus, [OrderStatus::Rejected, OrderStatus::Cancelled], true)
            && in_array($oldStatus, [OrderStatus::Pending, OrderStatus::Accepted, OrderStatus::Processing], true);
    }

    private function restoreStock(Order $order): void
    {
        $order->items->each(function (OrderItem $item): void {
            $item->product?->increment('stock', $item->quantity);
        });
    }

    private function creditSellers(Order $order, ?string $note): void
    {
        $this->sellerTotals($order)->each(function (float $amount, int|string $sellerId) use ($order, $note): void {
            $seller = Seller::query()->find((int) $sellerId);

            if (! $seller) {
                return;
            }

            $seller->increment('balance', $amount);
            $seller->transactions()->create([
                'order_id' => $order->id,
                'amount' => $amount,
                'type' => 'addition',
                'description' => __('orders.transactions.seller_credit', [
                    'order' => $order->id,
                    'note' => $note ?: __('common_not_specified'),
                ]),
            ]);
        });
    }

    private function debitSellers(Order $order, ?string $note): void
    {
        $this->sellerTotals($order)->each(function (float $amount, int|string $sellerId) use ($order, $note): void {
            $seller = Seller::query()->find((int) $sellerId);

            if (! $seller) {
                return;
            }

            $seller->decrement('balance', $amount);
            $seller->transactions()->create([
                'order_id' => $order->id,
                'amount' => $amount,
                'type' => 'deduction',
                'description' => __('orders.transactions.seller_debit', [
                    'order' => $order->id,
                    'note' => $note ?: __('common_not_specified'),
                ]),
            ]);
        });
    }

    /**
     * @return Collection<int, float>
     */
    private function sellerTotals(Order $order): Collection
    {
        return $order->items
            ->groupBy('seller_id')
            ->map(fn (Collection $items): float => (float) $items->sum('total_price'));
    }

    private function sendNotifications(OrderStatusHistory $history, ?Authenticatable $actor): void
    {
        $recipients = collect([$history->order->buyer])
            ->merge($history->order->items->pluck('seller'))
            ->filter()
            ->reject(fn (Authenticatable $recipient): bool => $this->sameActor($recipient, $actor))
            ->unique(fn (Authenticatable $recipient): string => $recipient::class.':'.$recipient->getAuthIdentifier())
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $this->sendNotification->handle($recipients, new OrderStatusChangedNotification($history));
    }

    private function logSpecificOrderStatusAction(
        Order $order,
        OrderStatus $oldStatus,
        OrderStatus $newStatus,
        ?Authenticatable $actor,
        ?string $reason,
        ?string $note,
    ): void {
        $action = match ($newStatus) {
            OrderStatus::Cancelled => 'order.cancelled',
            OrderStatus::Refunded => 'order.refunded',
            OrderStatus::Disputed => 'order.dispute_opened',
            default => null,
        };

        if ($action === null) {
            return;
        }

        $this->auditLogService->log(
            actor: $actor,
            action: $action,
            auditable: $order,
            oldValues: ['status' => $oldStatus->value],
            newValues: ['status' => $newStatus->value],
            metadata: [
                'source' => 'order_status_action',
                'order_total' => $order->order_total,
                'buyer_id' => $order->buyer_id,
                'seller_ids' => $order->items->pluck('seller_id')->unique()->values()->all(),
                'note' => $note,
            ],
            reason: $reason,
        );
    }

    private function sameActor(Authenticatable $recipient, ?Authenticatable $actor): bool
    {
        return $actor !== null
            && $recipient::class === $actor::class
            && (string) $recipient->getAuthIdentifier() === (string) $actor->getAuthIdentifier();
    }

    private function actorId(?Authenticatable $actor): ?int
    {
        return $actor ? (int) $actor->getAuthIdentifier() : null;
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
