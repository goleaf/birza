<?php

namespace App\Livewire\Backend\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['buyer', 'orderItems.product', 'orderItems.seller']);
    }

    public function render()
    {
        return view('backend.orders.show', [
            'order' => $this->order,
        ]);
    }
}


