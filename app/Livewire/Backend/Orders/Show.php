<?php

namespace App\Livewire\Backend\Orders;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\AuditLog;
use App\Models\Order;
use App\Support\LocaleFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    use InteractsWithWireUi;

    public Order $order;

    public ?string $nextStatus = null;

    public ?string $statusReason = null;

    public ?string $statusNote = null;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['buyer', 'orderItems.product.primaryImage', 'orderItems.seller', 'orderBundles.items.product.primaryImage', 'statusHistory']);
        $this->nextStatus = $this->statusOptions()[0]['id'] ?? null;
    }

    public function changeStatus(ChangeOrderStatusAction $changeOrderStatusAction): void
    {
        $validated = $this->validate([
            'nextStatus' => ['required', Rule::in(OrderStatus::values())],
            'statusReason' => ['required', 'string', 'max:500'],
            'statusNote' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'nextStatus' => __('orders.status.label'),
            'statusReason' => __('audit_logs.reason'),
            'statusNote' => __('orders.status.note'),
        ]);

        $changeOrderStatusAction->handle(
            order: $this->order,
            nextStatus: OrderStatus::from($validated['nextStatus']),
            actor: Auth::guard('admin')->user(),
            reason: $validated['statusReason'],
            note: $validated['statusNote'] ?? null,
        );

        $this->order = $this->order->fresh(['buyer', 'orderItems.product.primaryImage', 'orderItems.seller', 'orderBundles.items.product.primaryImage', 'statusHistory']);
        $this->nextStatus = $this->statusOptions()[0]['id'] ?? null;
        $this->statusReason = null;
        $this->statusNote = null;

        $this->notifySuccess(__('orders.status.messages.updated'));
    }

    public function render(): View
    {
        return view('backend.orders.show', [
            'auditLogs' => AuditLog::query()
                ->entity($this->order)
                ->with('actor')
                ->latest('created_at')
                ->limit(10)
                ->get(),
            'order' => $this->order,
            'statusClass' => $this->order->paymentStatusBadgeClass(),
            'orderStatusClass' => $this->order->statusBadgeClass(),
            'statusOptions' => $this->statusOptions(),
            'buyerDetails' => $this->buyerDetails(),
            'paymentDetails' => $this->paymentDetails(),
            'deletedProductCount' => $this->order->orderItems
                ->filter(fn ($item): bool => $item->product?->trashed() === true)
                ->count(),
        ]);
    }

    /**
     * @return array<int, array{icon: string, value: string, label: string}>
     */
    protected function buyerDetails(): array
    {
        if (! $this->order->buyer) {
            return [[
                'icon' => 'o-user',
                'value' => __('orders_buyer_not_found'),
                'label' => __('buyer_buyer_information'),
            ]];
        }

        return [
            [
                'icon' => 'o-building-office-2',
                'value' => $this->order->buyer->company_name ?: __('common_not_specified'),
                'label' => __('auth_company_name'),
            ],
            [
                'icon' => 'o-user',
                'value' => $this->order->buyer->name ?: __('common_not_specified'),
                'label' => __('common_name'),
            ],
            [
                'icon' => 'o-envelope',
                'value' => $this->order->buyer->email ?: __('common_not_specified'),
                'label' => __('common_email'),
            ],
            [
                'icon' => 'o-phone',
                'value' => $this->order->buyer->phone ?: __('common_not_specified'),
                'label' => __('sellers_phone'),
            ],
        ];
    }

    /**
     * @return array<int, array{icon: string, value: string, label: string}>
     */
    protected function paymentDetails(): array
    {
        return [
            [
                'icon' => 'o-credit-card',
                'value' => $this->order->payment_method ?: __('common_not_specified'),
                'label' => __('orders_payment_method'),
            ],
            [
                'icon' => 'o-banknotes',
                'value' => LocaleFormatter::currency($this->order->order_total),
                'label' => __('orders_order_total'),
            ],
            [
                'icon' => 'o-calendar',
                'value' => LocaleFormatter::dateTime($this->order->created_at),
                'label' => __('orders_placed_on'),
            ],
        ];
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    protected function statusOptions(): array
    {
        return collect($this->order->lifecycleStatus()->allowedNextStatuses())
            ->filter(fn (OrderStatus $status): bool => $status->canBeChangedBy(OrderStatusActorRole::Admin))
            ->map(fn (OrderStatus $status): array => [
                'id' => $status->value,
                'name' => $status->label(),
            ])
            ->values()
            ->all();
    }
}
