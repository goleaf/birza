<?php

namespace App\Livewire\Frontend\Seller\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    public Order $order;
    public $orderItems;
    public ?string $comment = null;

    public function mount(Order $order): void
    {
        $orderItems = OrderItem::with(['order', 'product'])
            ->where('seller_id', Auth::guard('seller')->id())
            ->where('order_id', $order->id)
            ->get();

        if ($orderItems->isEmpty()) {
            abort(403, 'Unauthorized access to order');
        }

        $this->order = $order;
        $this->orderItems = $orderItems;
    }

    public function updateStatus(string $status): void
    {
        $seller = Auth::guard('seller')->user();

        if (! $seller) {
            abort(403);
        }

        // Only allow simple transitions from the UI.
        if (! in_array($status, [Order::STATUS['PAID'], Order::STATUS['CANCELLED']], true)) {
            $this->addError('status', __('common.error_occurred'));
            return;
        }

        // Ensure seller owns items in this order.
        $hasItems = OrderItem::query()
            ->where('seller_id', $seller->id)
            ->where('order_id', $this->order->id)
            ->exists();

        if (! $hasItems) {
            session()->flash('error', __('orders.unauthorized_update'));
            return;
        }

        // Only allow changing pending orders (matches current UI expectation).
        if ($this->order->payment_status !== Order::STATUS['PENDING']) {
            session()->flash('error', __('orders.status_cannot_be_changed'));
            return;
        }

        $this->validate([
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $totalAmount = (float) $this->orderItems->sum('total_price');
        $comment = trim((string) ($this->comment ?? ''));

        DB::transaction(function () use ($seller, $status, $totalAmount, $comment) {
            $oldStatus = $this->order->payment_status;

            if ($status === Order::STATUS['PAID'] && $oldStatus === Order::STATUS['PENDING']) {
                $seller->increment('balance', $totalAmount);

                $seller->transactions()->create([
                    'order_id' => $this->order->id,
                    'amount' => $totalAmount,
                    'type' => 'addition',
                    'description' => trim("Order #{$this->order->id} confirmed - Balance added" . ($comment ? " ({$comment})" : '')),
                ]);
            } elseif ($status === Order::STATUS['CANCELLED']) {
                // No balance action needed because only pending orders can be changed from this UI.
            }

            $this->order->update([
                'payment_status' => $status,
                'status' => $status,
            ]);
        });

        $this->order->refresh();
        $this->comment = null;

        session()->flash('success', __('orders.status_updated'));
    }

    public function render()
    {
        return view('frontend.seller.orders.show', [
            'order' => $this->order,
            'orderItems' => $this->orderItems,
            'timeline' => $this->getOrderTimeline($this->order),
            'orderStatuses' => Order::STATUS,
        ]);
    }

    protected function getOrderTimeline(Order $order): array
    {
        return [
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }
}


