<?php

namespace App\Livewire\Backend\Sellers;

use App\Models\Order;
use App\Models\Users\Seller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Orders extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Seller $seller;

    public int $perPage = 15;

    public function mount($seller): void
    {
        $this->seller = $seller instanceof Seller ? $seller : Seller::findOrFail($seller);

        $this->authorize('view', $this->seller);
        $this->authorize('viewAny', Order::class);
    }

    public function render()
    {
        $orders = Order::query()
            ->select(['orders.id', 'orders.buyer_id', 'orders.payment_status', 'orders.status', 'orders.created_at', 'orders.order_total'])
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
            ->paginate($this->perPage)
            ->withQueryString();

        return view('backend.sellers.orders', [
            'orders' => $orders,
            'seller' => $this->seller,
        ]);
    }
}
