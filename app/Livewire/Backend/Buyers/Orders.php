<?php

namespace App\Livewire\Backend\Buyers;

use App\Enums\OrderStatus;
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

        $query = Order::query()
            ->select(['id', 'buyer_id', 'order_total', 'payment_status', 'status', 'created_at'])
            ->where('buyer_id', $this->buyer->id);

        $status = $request->filled('status')
            ? OrderStatus::tryFrom((string) $request->status)
            : null;

        if ($status !== null) {
            $query->where('status', $status->value);
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
