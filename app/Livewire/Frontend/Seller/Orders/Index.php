<?php

namespace App\Livewire\Frontend\Seller\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    public function render()
    {
        $seller = Auth::guard('seller')->user();

        $orderStatuses = Order::STATUS;
        $status = request()->get('status');
        $dateFrom = request()->get('date_from');
        $dateTo = request()->get('date_to');

        $filters = [
            'status' => $status,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];

        $ordersData = $this->getOrdersData($seller, $status, $dateFrom, $dateTo);

        return view('frontend.seller.orders.index', [
            'ordersData' => $ordersData,
            'filters' => $filters,
            'orderStatuses' => $orderStatuses,
        ]);
    }

    protected function getOrdersData($seller, $status = null, $dateFrom = null, $dateTo = null): array
    {
        $query = OrderItem::with(['order', 'order.buyer', 'product'])
            ->where('seller_id', $seller->id);

        if ($dateFrom) {
            $query->whereHas('order', function ($q) use ($dateFrom) {
                $q->whereDate('created_at', '>=', Carbon::parse($dateFrom));
            });
        }

        if ($dateTo) {
            $query->whereHas('order', function ($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', Carbon::parse($dateTo));
            });
        }

        if ($status) {
            $query->whereHas('order', function ($q) use ($status) {
                $q->where('payment_status', $status);
            });
        }

        $orderItems = $query->get();
        $orders = $orderItems->map(fn ($item) => $item->order)->unique('id');

        return [
            'all' => $orders,
            'pending' => $orders->where('payment_status', Order::STATUS['PENDING'])->count(),
            'processing' => $orders->where('payment_status', Order::STATUS['PROCESSING'])->count(),
            'shipped' => $orders->where('payment_status', Order::STATUS['SHIPPED'])->count(),
            'delivered' => $orders->where('payment_status', Order::STATUS['DELIVERED'])->count(),
            'cancelled' => $orders->where('payment_status', Order::STATUS['CANCELLED'])->count(),
            'refunded' => $orders->where('payment_status', Order::STATUS['REFUNDED'])->count(),
            'total' => $orders->count(),
            'items' => $orderItems->map(function ($item) {
                return [
                    'order' => $item->order,
                    'product' => $item->product,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'buyer' => $item->order->buyer,
                ];
            }),
            'totalRevenue' => $orders->where('payment_status', Order::STATUS['PAID'])->sum('order_total'),
            'revenueStats' => $this->getRevenueStatistics($seller),
            'topProducts' => $this->getTopSellingProducts($seller),
            'averageOrderValue' => $this->calculateAverageOrderValue($orders),
        ];
    }

    protected function getRevenueStatistics($seller): array
    {
        $today = Carbon::today();

        return [
            'daily' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function ($q) use ($today) {
                    $q->whereDate('created_at', $today)
                        ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price'),
            'weekly' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function ($q) use ($today) {
                    $q->whereBetween('created_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
                        ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price'),
            'monthly' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function ($q) use ($today) {
                    $q->whereMonth('created_at', $today->month)
                        ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price'),
            'yearly' => OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function ($q) use ($today) {
                    $q->whereYear('created_at', $today->year)
                        ->where('payment_status', Order::STATUS['PAID']);
                })
                ->sum('total_price'),
        ];
    }

    protected function getTopSellingProducts($seller, int $limit = 5)
    {
        return OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', function ($q) {
                $q->where('payment_status', Order::STATUS['PAID']);
            })
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    protected function calculateAverageOrderValue($orders): float
    {
        $paidOrders = $orders->where('payment_status', Order::STATUS['PAID']);

        if ($paidOrders->isEmpty()) {
            return 0;
        }

        return (float) $paidOrders->avg('order_total');
    }
}


