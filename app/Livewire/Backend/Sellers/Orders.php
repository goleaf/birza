<?php

namespace App\Livewire\Backend\Sellers;

use App\Models\Order;
use App\Models\Users\Seller;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Orders extends Component
{
    public Seller $seller;

    public function mount($seller): void
    {
        $this->seller = $seller instanceof Seller ? $seller : Seller::findOrFail($seller);
    }

    public function render()
    {
        $orders = Order::query()
            ->select(['orders.id', 'orders.buyer_id', 'orders.payment_status', 'orders.created_at', 'orders.order_total'])
            ->whereHas('orderItems', function ($query) {
                $query->where('seller_id', $this->seller->id);
            })
            ->with([
                'buyer:id,name,email,company_name',
                'orderItems' => function ($query) {
                    $query->select(['id', 'order_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'seller_id'])
                        ->where('seller_id', $this->seller->id)
                        ->with('product:id,name');
                },
            ])
            ->latest('orders.created_at')
            ->get();

        return view('backend.sellers.orders', [
            'orders' => $orders,
            'seller' => $this->seller,
        ]);
    }
}
