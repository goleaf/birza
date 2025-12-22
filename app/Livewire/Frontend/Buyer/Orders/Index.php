<?php

namespace App\Livewire\Frontend\Buyer\Orders;

use App\Models\Order;
use App\Models\Users\Buyer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    public function cancelOrder(int $orderId): void
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $order = Order::with(['items.product'])
            ->where('buyer_id', $buyer->id)
            ->findOrFail($orderId);

        if ($order->payment_status !== Order::STATUS['PENDING']) {
            session()->flash('error', __('orders.messages.cannot_cancel'));
            return;
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => Order::STATUS['CANCELLED'],
                'status' => Order::STATUS['CANCELLED'],
            ]);

            foreach ($order->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }
        });

        session()->flash('success', __('orders.messages.cancelled_success'));
    }

    public function render()
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $orderStatuses = Order::STATUS;
        $status = request()->get('status');
        $dateFrom = request()->get('date_from');
        $dateTo = request()->get('date_to');

        $filters = [
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];

        $ordersData = $this->getOrdersData($buyer, $status, $dateFrom, $dateTo);

        return view('frontend.buyer.orders.index', [
            'ordersData' => $ordersData,
            'filters' => $filters,
            'orderStatuses' => $orderStatuses,
        ]);
    }

    protected function getOrdersData(Buyer $buyer, $status = null, $dateFrom = null, $dateTo = null): array
    {
        $query = Order::where('buyer_id', $buyer->id);

        if ($status) {
            $query->where('payment_status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return [
            'all' => $orders,
            'pending' => $orders->where('payment_status', Order::STATUS['PENDING'])->count(),
            'processing' => $orders->where('payment_status', Order::STATUS['PROCESSING'])->count(),
            'shipped' => $orders->where('payment_status', Order::STATUS['SHIPPED'])->count(),
            'delivered' => $orders->where('payment_status', Order::STATUS['DELIVERED'])->count(),
            'cancelled' => $orders->where('payment_status', Order::STATUS['CANCELLED'])->count(),
            'refunded' => $orders->where('payment_status', Order::STATUS['REFUNDED'])->count(),
            'paid' => $orders->where('payment_status', Order::STATUS['PAID'])->count(),
            'failed' => $orders->where('payment_status', Order::STATUS['FAILED'])->count(),
            'total' => $orders->count(),
            'totalSpent' => $orders->where('payment_status', Order::STATUS['PAID'])->sum('order_total'),
        ];
    }
}


