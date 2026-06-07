<?php

namespace App\Livewire\Frontend\Seller;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Users\Seller;
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
    public int $winRateRating = 4;

    public int $marketTrendRating = 4;

    public int $volatilityRating = 3;

    public int $competitionRating = 4;

    public array $monthlySalesChart = [];

    public function render(): View
    {
        /** @var Seller $seller */
        $seller = Auth::guard('seller')->user();
        $seller->loadMissing('categories:id');

        $categoriesData = $this->getCategoriesData($seller);
        $ordersData = $this->getOrdersData($seller);

        return view('frontend.seller.dashboard.index', [
            'seller' => $seller,
            'categoriesData' => $categoriesData,
            'ordersData' => $ordersData,
            'recentNotifications' => $this->recentNotifications($seller),
        ]);
    }

    protected function getCategoriesData(Seller $seller)
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

    protected function getOrdersData(Seller $seller): array
    {
        $ordersQuery = $this->sellerOrdersQuery($seller);
        $this->monthlySalesChart = $this->buildMonthlySalesChart($seller);

        return [
            'total' => (clone $ordersQuery)->count(),
            'counts' => $this->statusCounts((clone $ordersQuery)),
            'totalRevenue' => OrderItem::query()
                ->forSeller($seller)
                ->whereHas('order', fn ($query) => $query->paid())
                ->sum('total_price'),
            'recent' => $this->recentOrderItems($seller)
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
            'allOrders' => collect(),
        ];
    }

    protected function sellerOrdersQuery(Seller $seller): Builder
    {
        return Order::query()
            ->summaryColumns()
            ->whereIn('id', OrderItem::query()->select('order_id')->forSeller($seller));
    }

    protected function recentOrderItems(Seller $seller): Collection
    {
        return OrderItem::query()
            ->select(['id', 'order_id', 'product_id', 'seller_id', 'quantity', 'unit_price', 'total_price', 'created_at'])
            ->forSeller($seller)
            ->with([
                'order:id,buyer_id,payment_status,status,order_total,created_at,updated_at',
                'order.buyer:id,name,company_name',
                'product' => function ($query): void {
                    $query->select(['id', 'name', 'product_image'])
                        ->with('primaryImage:id,product_id,disk,path,variants,is_primary,sort_order');
                },
            ])
            ->latest()
            ->limit(5)
            ->get();
    }

    protected function recentNotifications(Seller $seller): Collection
    {
        return $seller->notifications()
            ->select(['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildMonthlySalesChart(Seller $seller): array
    {
        $months = $this->recentChartMonths();

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
                                    (float) $this->monthlyOrderItemsQuery($seller, $month)
                                        ->whereHas('order', fn ($query) => $query->paid())
                                        ->sum('total_price'),
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
                                fn (Carbon $month): int => $this->monthlyOrdersQuery($seller, $month)
                                    ->where('payment_status', OrderPaymentStatus::Paid->value)
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

    protected function monthlyOrdersQuery(Seller $seller, Carbon $month): Builder
    {
        return $this->sellerOrdersQuery($seller)
            ->where('created_at', '>=', $month->copy()->startOfMonth())
            ->where('created_at', '<=', $month->copy()->endOfMonth());
    }

    protected function monthlyOrderItemsQuery(Seller $seller, Carbon $month): Builder
    {
        return OrderItem::query()
            ->forSeller($seller)
            ->whereHas('order', function ($query) use ($month): void {
                $query
                    ->where('created_at', '>=', $month->copy()->startOfMonth())
                    ->where('created_at', '<=', $month->copy()->endOfMonth());
            });
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
}
