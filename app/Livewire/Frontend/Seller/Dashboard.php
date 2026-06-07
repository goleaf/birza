<?php

namespace App\Livewire\Frontend\Seller;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Dashboard extends Component
{
    public int $winRateRating = 4;

    public int $marketTrendRating = 4;

    public int $volatilityRating = 3;

    public int $competitionRating = 4;

    public array $monthlySalesChart = [];

    public function render(): View
    {
        $seller = Auth::guard('seller')->user();

        $categoriesData = $this->getCategoriesData($seller);
        $ordersData = $this->getOrdersData($seller);

        return view('frontend.seller.dashboard.index', [
            'seller' => $seller,
            'categoriesData' => $categoriesData,
            'ordersData' => $ordersData,
        ]);
    }

    protected function getCategoriesData($seller)
    {
        $categories = $seller->categories()
            ->withRelationsForSeller()
            ->with('parent')
            ->get();

        $groupedCategories = $categories->groupBy(function ($category) {
            return $category->parent_category_id ?? $category->id;
        });

        return $groupedCategories->map(function ($categories) {
            $firstCategory = $categories->first();
            $parentCategory = $firstCategory->parent ?? $firstCategory;
            $isSubcategory = (bool) $firstCategory->parent;

            return [
                'parentCategory' => $parentCategory,
                'categories' => $categories,
                'isSubcategory' => $isSubcategory,
            ];
        });
    }

    protected function getOrdersData($seller): array
    {
        $orderItems = OrderItem::with([
            'order',
            'order.buyer',
            'product.primaryImage',
        ])
            ->where('seller_id', $seller->id)
            ->latest()
            ->get();

        $orders = $orderItems->pluck('order')->unique('id')->values();
        $paidOrders = $orders->where('payment_status', Order::STATUS['PAID']);
        $paidOrderItems = $orderItems->filter(
            fn (OrderItem $item): bool => $item->order?->payment_status === Order::STATUS['PAID']
        );

        $this->monthlySalesChart = $this->buildMonthlySalesChart($orders, $paidOrderItems);

        return [
            'total' => $orders->count(),
            'pending' => $orders->where('payment_status', Order::STATUS['PENDING'])->count(),
            'paid' => $paidOrders->count(),
            'failed' => $orders->where('payment_status', Order::STATUS['FAILED'])->count(),
            'totalRevenue' => $paidOrderItems->sum('total_price'),
            'recent' => $orderItems->take(5)
                ->map(function ($item) {
                    return [
                        'order' => $item->order,
                        'product' => $item->product,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'buyer' => $item->order->buyer,
                    ];
                }),
            'allOrders' => $orderItems->groupBy('order_id')
                ->map(function ($items) {
                    $order = $items->first()->order;

                    return [
                        'order' => $order,
                        'items' => $items->map(function ($item) {
                            return [
                                'product' => $item->product,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price,
                            ];
                        }),
                        'buyer' => $order->buyer,
                        'total' => $items->sum('total_price'),
                    ];
                }),
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @param  Collection<int, OrderItem>  $paidOrderItems
     * @return array<string, mixed>
     */
    protected function buildMonthlySalesChart(Collection $orders, Collection $paidOrderItems): array
    {
        $months = $this->recentChartMonths();
        $ordersByMonth = $orders->groupBy(
            fn (Order $order): string => (string) $order->created_at?->format('Y-m')
        );
        $paidItemsByMonth = $paidOrderItems->groupBy(
            fn (OrderItem $item): string => (string) $item->order?->created_at?->format('Y-m')
        );

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $months
                    ->map(fn (Carbon $month): string => $this->monthLabel($month))
                    ->all(),
                'datasets' => [
                    [
                        'label' => __('dashboard_total_revenue'),
                        'data' => $months
                            ->map(
                                fn (Carbon $month): float => round(
                                    (float) $paidItemsByMonth->get($month->format('Y-m'), collect())->sum('total_price'),
                                    2
                                )
                            )
                            ->all(),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.72)',
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                        'borderWidth' => 1,
                        'borderRadius' => 12,
                        'borderSkipped' => false,
                        'maxBarThickness' => 42,
                        'yAxisID' => 'y',
                    ],
                    [
                        'type' => 'line',
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
                        'tension' => 0.3,
                        'pointRadius' => 3,
                        'pointHoverRadius' => 5,
                        'yAxisID' => 'y1',
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
                    ],
                    'y1' => [
                        'beginAtZero' => true,
                        'position' => 'right',
                        'grid' => [
                            'drawOnChartArea' => false,
                        ],
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
}
