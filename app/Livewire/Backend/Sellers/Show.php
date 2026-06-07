<?php

namespace App\Livewire\Backend\Sellers;

use App\Models\Order;
use App\Models\Users\Seller;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    public Seller $seller;

    public function mount(Seller $seller): void
    {
        $this->seller = $seller;
    }

    public function render()
    {
        $products = $this->seller->products()
            ->select('id', 'name', 'price', 'is_active', 'product_image', 'category_id')
            ->with('category:id,category_name')
            ->withCount('orderItems')
            ->latest()
            ->paginate(10);

        $orders = $this->seller->orders()
            ->with([
                'buyer',
                'orderItems.product',
                'orderItems.seller',
            ])
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return view('backend.sellers.show', [
            'seller' => $this->seller,
            'products' => $products,
            'recentOrders' => $orders,
            'orderStatuses' => Order::STATUS,
        ]);
    }
}
