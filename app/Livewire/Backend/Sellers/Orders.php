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
        $orders = Order::whereHas('orderItems.product', function ($query) {
            $query->where('seller_id', $this->seller->id);
        })
            ->with([
                'orderItems.product',
                'buyer',
            ])
            ->get();

        return view('backend.sellers.orders', [
            'orders' => $orders,
            'seller' => $this->seller,
            'orderStatuses' => Order::STATUS,
        ]);
    }
}


