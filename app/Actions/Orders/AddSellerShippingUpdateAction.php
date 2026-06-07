<?php

namespace App\Actions\Orders;

use App\Actions\Notifications\SendMarketplaceNotificationAction;
use App\Enums\OrderEventType;
use App\Models\Order;
use App\Models\Users\Seller;
use App\Notifications\Marketplace\OrderTrackingAddedNotification;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AddSellerShippingUpdateAction
{
    public function __construct(
        private readonly CreateOrderEventAction $createOrderEvent,
        private readonly SendMarketplaceNotificationAction $sendNotification,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Order $order, Seller $seller, array $data): Order
    {
        $validated = $this->validated($data);

        $result = DB::transaction(function () use ($order, $seller, $validated): array {
            $lockedOrder = Order::query()
                ->with(['buyer', 'items.seller'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if (! $seller->can('addShippingUpdate', $lockedOrder)) {
                throw new AuthorizationException(__('orders.tracking.messages.unauthorized'));
            }

            if (! $lockedOrder->canReceiveShippingUpdate()) {
                throw ValidationException::withMessages([
                    'tracking_number' => __('orders.tracking.messages.status_not_allowed'),
                ]);
            }

            $oldValues = $this->trackingSnapshot($lockedOrder);
            $newValues = $this->normalizedTrackingValues($validated);

            if ($this->hasNoData($newValues, $validated['public_note'] ?? null)) {
                throw ValidationException::withMessages([
                    'tracking_number' => __('orders.tracking.messages.at_least_one_field'),
                ]);
            }

            $lockedOrder->forceFill($newValues)->save();
            $lockedOrder->refresh();

            $changedValues = $this->auditLogService->changedValues($oldValues, $this->trackingSnapshot($lockedOrder));
            $trackingNumberChanged = array_key_exists('tracking_number', $changedValues['new']);
            $eventType = $trackingNumberChanged ? OrderEventType::TrackingUpdated : OrderEventType::ShippingUpdated;

            $event = $this->createOrderEvent->handle(
                order: $lockedOrder,
                eventType: $eventType,
                actor: $seller,
                publicNote: $validated['public_note'] ?? null,
                metadata: [
                    'changed' => array_keys($changedValues['new']),
                    'tracking_number' => $lockedOrder->tracking_number,
                    'carrier_name' => $lockedOrder->carrier_name,
                    'estimated_delivery_date' => $lockedOrder->estimated_delivery_date?->toDateString(),
                ],
            );

            $this->auditLogService->log(
                actor: $seller,
                action: $trackingNumberChanged ? 'order.tracking_updated' : 'order.shipping_updated',
                auditable: $lockedOrder,
                oldValues: $changedValues['old'],
                newValues: $changedValues['new'],
                metadata: [
                    'source' => 'seller_shipping_update',
                    'event_id' => $event->id,
                    'seller_ids' => $lockedOrder->items->pluck('seller_id')->unique()->values()->all(),
                ],
            );

            return [$lockedOrder->fresh(['buyer', 'items.seller', 'events']), $trackingNumberChanged];
        });

        /** @var Order $updatedOrder */
        [$updatedOrder, $trackingNumberChanged] = $result;

        if ($trackingNumberChanged) {
            $this->sendNotification->handle($updatedOrder->buyer, new OrderTrackingAddedNotification($updatedOrder));
        }

        return $updatedOrder;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{tracking_number: ?string, carrier_name: ?string, estimated_delivery_date: ?string, public_note: ?string}
     */
    private function validated(array $data): array
    {
        $validator = Validator::make($data, [
            'tracking_number' => ['nullable', 'string', 'max:120', 'regex:/\A[A-Za-z0-9][A-Za-z0-9 ._\-\/]{0,119}\z/'],
            'carrier_name' => ['nullable', 'string', 'max:120', 'not_regex:/[<>]/'],
            'estimated_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'public_note' => ['nullable', 'string', 'max:500', 'not_regex:/[<>]/'],
        ], [], [
            'tracking_number' => __('orders.tracking.number'),
            'carrier_name' => __('orders.tracking.carrier'),
            'estimated_delivery_date' => __('orders.tracking.estimated_delivery'),
            'public_note' => __('orders.notes.public_note'),
        ]);

        return $validator->validate();
    }

    /**
     * @param  array{tracking_number?: ?string, carrier_name?: ?string, estimated_delivery_date?: ?string}  $validated
     * @return array{tracking_number: ?string, carrier_name: ?string, estimated_delivery_date: ?string}
     */
    private function normalizedTrackingValues(array $validated): array
    {
        return [
            'tracking_number' => $this->cleanText($validated['tracking_number'] ?? null),
            'carrier_name' => $this->cleanText($validated['carrier_name'] ?? null),
            'estimated_delivery_date' => filled($validated['estimated_delivery_date'] ?? null)
                ? Carbon::parse((string) $validated['estimated_delivery_date'])->toDateString()
                : null,
        ];
    }

    /**
     * @return array{tracking_number: ?string, carrier_name: ?string, estimated_delivery_date: ?string}
     */
    private function trackingSnapshot(Order $order): array
    {
        return [
            'tracking_number' => $order->tracking_number,
            'carrier_name' => $order->carrier_name,
            'estimated_delivery_date' => $order->estimated_delivery_date?->toDateString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function hasNoData(array $values, ?string $publicNote): bool
    {
        return collect($values)
            ->filter(fn (mixed $value): bool => filled($value))
            ->isEmpty()
            && blank($this->cleanText($publicNote));
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : $value;
    }
}
