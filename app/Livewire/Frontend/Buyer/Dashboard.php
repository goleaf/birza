<?php

namespace App\Livewire\Frontend\Buyer;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
            'recentNotifications' => $this->recentNotifications($buyer),
            'recentStockAlerts' => $this->recentStockAlerts($buyer),
            'wishlistSummary' => $this->wishlistSummary($buyer),
        ]);
    }

    protected function getOrdersData(Buyer $buyer): array
    {
        $baseQuery = Order::query()
            ->summaryColumns()
            ->forBuyer($buyer);

        $today = Carbon::today();
        $paidQuery = (clone $baseQuery)->where('payment_status', OrderPaymentStatus::Paid->value);
        $totalOrders = (clone $baseQuery)->count();
        $paidOrdersCount = (clone $paidQuery)->count();
        $recentOrders = $this->recentOrders($buyer);

        $this->salesPerformanceChart = $this->buildSalesPerformanceChart($buyer);

        return [
            'all' => collect(),
            'counts' => $this->statusCounts((clone $baseQuery)),
            'total' => $totalOrders,
            'items' => collect(),
            'totalSpent' => (clone $paidQuery)->sum('order_total'),
            'recentOrders' => $recentOrders,
            'statistics' => [
                'daily' => [
                    'count' => (clone $baseQuery)->where('created_at', '>=', $today)->count(),
                    'amount' => (clone $paidQuery)->where('created_at', '>=', $today)->sum('order_total'),
                ],
                'weekly' => [
                    'count' => (clone $baseQuery)->where('created_at', '>=', $today->copy()->subWeek())->count(),
                    'amount' => (clone $paidQuery)->where('created_at', '>=', $today->copy()->subWeek())->sum('order_total'),
                ],
                'monthly' => [
                    'count' => (clone $baseQuery)->where('created_at', '>=', $today->copy()->subMonth())->count(),
                    'amount' => (clone $paidQuery)->where('created_at', '>=', $today->copy()->subMonth())->sum('order_total'),
                ],
            ],
            'insights' => [
                'averageOrderValue' => (clone $paidQuery)->avg('order_total') ?? 0,
                'highestOrderValue' => (clone $paidQuery)->max('order_total') ?? 0,
                'totalOrders' => $totalOrders,
                'successRate' => $totalOrders > 0
                    ? round(($paidOrdersCount / $totalOrders) * 100, 2)
                    : 0,
            ],
            'mostOrderedProducts' => $this->getMostOrderedProducts($buyer),
            'recentActivity' => [
                'lastOrder' => $recentOrders->first(),
                'recentOrders' => $recentOrders,
                'lastPaidOrder' => (clone $paidQuery)->latest()->first(),
            ],
        ];
    }

    protected function recentOrders(Buyer $buyer): Collection
    {
        return Order::query()
            ->summaryColumns()
            ->forBuyer($buyer)
            ->latest()
            ->limit(5)
            ->get();
    }

    protected function recentNotifications(Buyer $buyer): Collection
    {
        return $buyer->notifications()
            ->select(['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();
    }

    protected function recentStockAlerts(Buyer $buyer): Collection
    {
        return ProductStockAlert::query()
            ->forBuyer($buyer)
            ->select(['id', 'product_id', 'buyer_id', 'status', 'notified_at', 'created_at'])
            ->with([
                'product:id,name,seller_id,stock,unit,is_active,deleted_at,product_image',
                'product.seller:id,name,company_name,is_active,deleted_at',
            ])
            ->latest()
            ->limit(5)
            ->get();
    }

    protected function wishlistSummary(Buyer $buyer): Collection
    {
        return Wishlist::query()
            ->forBuyer($buyer)
            ->withCount('items')
            ->latest()
            ->limit(3)
            ->get();
    }

    protected function getMostOrderedProducts(Buyer $buyer): Collection
    {
        return OrderItem::query()
            ->select(['id', 'order_id', 'product_id', 'quantity', 'total_price'])
            ->whereHas('order', fn ($query) => $query->forBuyer($buyer))
            ->with(['product:id,name,product_image'])
            ->latest()
            ->limit(200)
            ->get()
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
     * @return array<string, mixed>
     */
    protected function buildSalesPerformanceChart(Buyer $buyer): array
    {
        $months = $this->recentChartMonths();

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
                                fn (Carbon $month): int => $this->monthlyOrdersQuery($buyer, $month)->count()
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
                                fn (Carbon $month): int => $this->monthlyOrdersQuery($buyer, $month)
                                    ->where('payment_status', OrderPaymentStatus::Paid->value)
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

    protected function monthlyOrdersQuery(Buyer $buyer, Carbon $month): Builder
    {
        return Order::query()
            ->forBuyer($buyer)
            ->where('created_at', '>=', $month->copy()->startOfMonth())
            ->where('created_at', '<=', $month->copy()->endOfMonth());
    }

    /**
     * @return array<string, int>
     */
    protected function statusCounts(Builder $query): array
    {
        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status): array => [
                $status->value => (clone $query)->where('status', $status->value)->count(),
            ])
            ->all();
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
