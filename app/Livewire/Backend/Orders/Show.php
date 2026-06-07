<?php

namespace App\Livewire\Backend\Orders;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['buyer', 'orderItems.product.primaryImage', 'orderItems.seller']);
    }

    public function render(): View
    {
        return view('backend.orders.show', [
            'order' => $this->order,
            'statusClass' => $this->order->paymentStatusBadgeClass(),
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
                'value' => number_format((float) $this->order->order_total, 2).' €',
                'label' => __('orders_order_total'),
            ],
            [
                'icon' => 'o-calendar',
                'value' => $this->order->created_at?->format('Y-m-d H:i') ?? __('common_not_specified'),
                'label' => __('orders_placed_on'),
            ],
        ];
    }
}
