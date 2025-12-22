<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\Order;
use App\Models\Users\Buyer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Orders extends Component
{
    public Buyer $buyer;

    public function mount(Buyer $buyer): void
    {
        $this->buyer = $buyer;
    }

    public function render()
    {
        $request = request();

        $query = Order::with(['items.product', 'items.seller'])
            ->where('buyer_id', $this->buyer->id);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return view('backend.buyers.orders', [
            'buyer' => $this->buyer,
            'orders' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }
}


