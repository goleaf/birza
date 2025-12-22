<?php

namespace App\Livewire\Frontend\Buyer\Orders;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    use InteractsWithWireUi;

    public Order $order;

    public function mount(Order $order): void
    {
        if ($order->buyer_id !== Auth::guard('buyer')->id()) {
            abort(403);
        }

        $this->order = $order->load(['items.product', 'items.seller']);
    }

    public function cancelOrder(): void
    {
        if ($this->order->buyer_id !== Auth::guard('buyer')->id()) {
            abort(403);
        }

        if ($this->order->payment_status !== Order::STATUS['PENDING']) {
            $this->notifyError(__('orders.messages.cannot_cancel'));
            return;
        }

        DB::transaction(function () {
            $this->order->update([
                'payment_status' => Order::STATUS['CANCELLED'],
                'status' => Order::STATUS['CANCELLED'],
            ]);

            foreach ($this->order->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }
        });

        $this->order->refresh()->load(['items.product', 'items.seller']);

        $this->notifySuccess(__('orders.messages.cancelled_success'));
    }

    public function confirmCancelOrder(): void
    {
        $this->confirmAction(
            title: __('orders.confirm_cancel'),
            description: __('orders.confirm_cancel'),
            acceptLabel: __('orders.cancel_order'),
            method: 'cancelOrder',
            icon: 'warning',
        );
    }

    public function render()
    {
        return view('frontend.buyer.orders.show', [
            'order' => $this->order,
        ]);
    }
}


