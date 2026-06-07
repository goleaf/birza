<?php

namespace App\Livewire\Frontend\Buyer;

use App\Models\Order;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Dashboard extends Component
{
    public array $salesPerformanceChart = [];

    public function render(): View
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $ordersData = $this->getOrdersData($buyer);

        return view('frontend.buyer.dashboard.index', [
            'buyer' => $buyer,
            'bannerSlides' => $this->getBannerSlides(),
            'ordersData' => $ordersData,
            'recentStockAlerts' => $this->recentStockAlerts($buyer),
        ]);
    }

    protected function getOrdersData(Buyer $buyer): array
    {
        $orders = $buyer->orders()
            ->withFullDetails()
            ->latest()
            ->get();

        $this->salesPerformanceChart = $this->buildSalesPerformanceChart($orders);

        $ordersByStatus = $orders->groupBy('payment_status');
        $today = Carbon::today();
        $paidOrders = $orders->where('payment_status', Order::STATUS['PAID']);

        return [
            'all' => $orders,
            'pending' => $ordersByStatus->get(Order::STATUS['PENDING'], collect())->count(),
            'paid' => $ordersByStatus->get(Order::STATUS['PAID'], collect())->count(),
            'failed' => $ordersByStatus->get(Order::STATUS['FAILED'], collect())->count(),
            'total' => $orders->count(),
            'items' => $orders->flatMap->orderItems,
            'totalSpent' => $paidOrders->sum('order_total'),
            'recentOrders' => $orders->take(5),
            'statistics' => [
                'daily' => [
                    'count' => $orders->where('created_at', '>=', $today)->count(),
                    'amount' => $orders->where('created_at', '>=', $today)
                        ->where('payment_status', Order::STATUS['PAID'])
                        ->sum('order_total'),
                ],
                'weekly' => [
                    'count' => $orders->where('created_at', '>=', $today->copy()->subWeek())->count(),
                    'amount' => $orders->where('created_at', '>=', $today->copy()->subWeek())
                        ->where('payment_status', Order::STATUS['PAID'])
                        ->sum('order_total'),
                ],
                'monthly' => [
                    'count' => $orders->where('created_at', '>=', $today->copy()->subMonth())->count(),
                    'amount' => $orders->where('created_at', '>=', $today->copy()->subMonth())
                        ->where('payment_status', Order::STATUS['PAID'])
                        ->sum('order_total'),
                ],
            ],
            'insights' => [
                'averageOrderValue' => $paidOrders->avg('order_total') ?? 0,
                'highestOrderValue' => $paidOrders->max('order_total') ?? 0,
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

    /**
     * @param  Collection<int, Order>  $orders
     * @return array<string, mixed>
     */
    protected function buildSalesPerformanceChart(Collection $orders): array
    {
        $months = $this->recentChartMonths();
        $ordersByMonth = $orders->groupBy(
            fn (Order $order): string => (string) $order->created_at?->format('Y-m')
        );

        return [
            'type' => 'line',
            'data' => [
                'labels' => $months
                    ->map(fn (Carbon $month): string => $this->monthLabel($month))
                    ->all(),
                'datasets' => [
                    [
                        'label' => __('dashboard_total_orders'),
                        'data' => $months
                            ->map(
                                fn (Carbon $month): int => $ordersByMonth->get($month->format('Y-m'), collect())->count()
                            )
                            ->all(),
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.12)',
                        'fill' => true,
                        'tension' => 0.35,
                        'pointRadius' => 3,
                        'pointHoverRadius' => 5,
                    ],
                    [
                        'label' => __('dashboard_paid_orders'),
                        'data' => $months
                            ->map(
                                fn (Carbon $month): int => $ordersByMonth
                                    ->get($month->format('Y-m'), collect())
                                    ->where('payment_status', Order::STATUS['PAID'])
                                    ->count()
                            )
                            ->all(),
                        'borderColor' => 'rgba(16, 185, 129, 1)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.18)',
                        'fill' => true,
                        'tension' => 0.35,
                        'pointRadius' => 3,
                        'pointHoverRadius' => 5,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => [
                            'usePointStyle' => true,
                            'boxWidth' => 10,
                        ],
                    ],
                ],
                'scales' => [
                    'x' => [
                        'grid' => [
                            'display' => false,
                        ],
                    ],
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'precision' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return Collection<int, Carbon>
     */
    protected function recentChartMonths(int $months = 6): Collection
    {
        return collect(range($months - 1, 0))
            ->map(fn (int $offset): Carbon => Carbon::now()->startOfMonth()->subMonths($offset));
    }

    protected function monthLabel(Carbon $month): string
    {
        return __('common_months_'.strtolower($month->format('M')));
    }

    protected function recentStockAlerts(Buyer $buyer): Collection
    {
        return ProductStockAlert::query()
            ->forBuyer($buyer)
            ->select(['id', 'product_id', 'buyer_id', 'status', 'notified_at', 'created_at'])
            ->with([
                'product:id,name,seller_id,stock,unit,is_active,deleted_at,product_image,product_additional_image,image_library',
                'product.seller:id,name,company_name,is_active,deleted_at',
            ])
            ->latest()
            ->limit(5)
            ->get();
    }

    protected function getBannerSlides(): array
    {
        return collect([
            __('dashboard_banner_1'),
            __('dashboard_banner_2'),
            __('dashboard_banner_3'),
        ])->map(fn (string $bannerTitle, int $index): array => [
            'image' => 'https://via.placeholder.com/600x400?text='.rawurlencode($bannerTitle),
            'alt' => $bannerTitle,
            'lazy' => $index > 0,
        ])->all();
    }
}
