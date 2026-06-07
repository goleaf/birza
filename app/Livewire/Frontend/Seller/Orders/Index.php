<?php

namespace App\Livewire\Frontend\Seller\Orders;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Users\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $status = '';

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_to')]
    public ?string $dateTo = null;

    public int $perPage = 15;

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        /** @var Seller $seller */
        $seller = Auth::guard('seller')->user();
        $ordersData = $this->getOrdersData($seller, $this->status, $this->dateFrom, $this->dateTo);

        return view('frontend.seller.orders.index', [
            'ordersData' => $ordersData,
            'filters' => [
                'status' => $this->status,
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
            ],
            'orderStatuses' => OrderStatus::cases(),
            'pendingStatus' => OrderStatus::Pending,
            'orderCalendarEvents' => $ordersData['calendarEvents'],
        ]);
    }

    protected function getOrdersData(
        Seller $seller,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $status = is_string($status) ? OrderStatus::tryFrom($status) : null;
        $query = $this->filteredOrdersQuery($seller, $status, $dateFrom, $dateTo);

        $orders = (clone $query)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        $calendarOrders = (clone $query)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $totalRevenue = $this->sellerOrderItemsQuery($seller, $status, $dateFrom, $dateTo)
            ->whereHas('order', fn ($orderQuery) => $orderQuery->paid())
            ->sum('total_price');

        $paidOrderCount = (clone $query)
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->count();

        return [
            'all' => $orders,
            'counts' => $this->statusCounts((clone $query)),
            'total' => (clone $query)->count(),
            'totalAmount' => $this->sellerOrderItemsQuery($seller, $status, $dateFrom, $dateTo)->sum('total_price'),
            'totalRevenue' => $totalRevenue,
            'averageOrderValue' => $paidOrderCount === 0
                ? 0
                : (float) $totalRevenue / $paidOrderCount,
            'calendarEvents' => $calendarOrders
                ->sortBy('created_at')
                ->map(fn (Order $order): array => $order->calendarEvent((float) $order->seller_total))
                ->values()
                ->all(),
        ];
    }

    protected function filteredOrdersQuery(
        Seller $seller,
        ?OrderStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Builder {
        $query = Order::query()
            ->summaryColumns()
            ->whereIn('id', OrderItem::query()->select('order_id')->forSeller($seller))
            ->placedBetween($dateFrom, $dateTo)
            ->withSum([
                'orderItems as seller_total' => fn ($itemQuery) => $itemQuery->forSeller($seller),
            ], 'total_price');

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query;
    }

    protected function sellerOrderItemsQuery(
        Seller $seller,
        ?OrderStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Builder {
        return OrderItem::query()
            ->forSeller($seller)
            ->whereHas('order', function ($orderQuery) use ($status, $dateFrom, $dateTo): void {
                $orderQuery->placedBetween($dateFrom, $dateTo);

                if ($status !== null) {
                    $orderQuery->where('status', $status->value);
                }
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
}
