<?php

namespace App\Livewire\Frontend\Buyer;

use App\Models\Order;
use App\Models\Users\Buyer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Dashboard extends Component
{
    public function render()
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $ordersData = $this->getOrdersData($buyer);

        return view('frontend.buyer.dashboard.index', [
            'buyer' => $buyer,
            'ordersData' => $ordersData,
        ]);
    }

    protected function getOrdersData(Buyer $buyer): array
    {
        $orders = $buyer->orders()
            ->withFullDetails()
            ->latest()
            ->get();

        $ordersByStatus = $orders->groupBy('status');
        $today = Carbon::today();
        $paidOrders = $orders->where('status', Order::STATUS['PAID']);

        return [
            'all' => $orders,
            'pending' => $ordersByStatus->get(Order::STATUS['PENDING'], collect())->count(),
            'paid' => $ordersByStatus->get(Order::STATUS['PAID'], collect())->count(),
            'failed' => $ordersByStatus->get(Order::STATUS['FAILED'], collect())->count(),
            'total' => $orders->count(),
            'items' => $orders->flatMap->orderItems,
            'totalSpent' => $orders->where('status', Order::STATUS['PAID'])->sum('total_price'),
            'recentOrders' => $orders->take(5),
            'statistics' => [
                'daily' => [
                    'count' => $orders->where('created_at', '>=', $today)->count(),
                    'amount' => $orders->where('created_at', '>=', $today)
                        ->where('status', Order::STATUS['PAID'])
                        ->sum('total_price'),
                ],
                'weekly' => [
                    'count' => $orders->where('created_at', '>=', $today->copy()->subWeek())->count(),
                    'amount' => $orders->where('created_at', '>=', $today->copy()->subWeek())
                        ->where('status', Order::STATUS['PAID'])
                        ->sum('total_price'),
                ],
                'monthly' => [
                    'count' => $orders->where('created_at', '>=', $today->copy()->subMonth())->count(),
                    'amount' => $orders->where('created_at', '>=', $today->copy()->subMonth())
                        ->where('status', Order::STATUS['PAID'])
                        ->sum('total_price'),
                ],
            ],
            'insights' => [
                'averageOrderValue' => $paidOrders->avg('total_price') ?? 0,
                'highestOrderValue' => $paidOrders->max('total_price') ?? 0,
                'totalOrders' => $orders->count(),
                'successRate' => $orders->count() > 0
                    ? round(($paidOrders->count() / $orders->count()) * 100, 2)
                    : 0,
            ],
            'mostOrderedProducts' => $this->getMostOrderedProducts($orders),
            'recentActivity' => [
                'lastOrder' => $orders->first(),
                'recentOrders' => $orders->take(5),
                'lastPaidOrder' => $paidOrders->first(),
            ],
        ];
    }

    protected function getMostOrderedProducts($orders)
    {
        return $orders->flatMap->orderItems
            ->groupBy('product_id')
            ->map(function ($items) {
                return [
                    'product' => $items->first()->product,
                    'total_quantity' => $items->sum('quantity'),
                    'total_spent' => $items->sum('total_price'),
                ];
            })
            ->sortByDesc('total_quantity')
            ->take(5);
    }
}


