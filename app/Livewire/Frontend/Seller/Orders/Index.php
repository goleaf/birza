<?php

namespace App\Livewire\Frontend\Seller\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Users\Seller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    #[Url(as: 'status')]
    public string $status = '';

    #[Url(as: 'date_from')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_to')]
    public ?string $dateTo = null;

    public function applyFilters(): void
    {
        //
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
            'orderStatuses' => Order::STATUS,
            'orderCalendarEvents' => $ordersData['all']
                ->sortBy('created_at')
                ->map(fn (Order $order): array => $order->calendarEvent((float) $order->seller_total))
                ->values()
                ->all(),
        ]);
    }

    protected function getOrdersData(
        Seller $seller,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $query = OrderItem::query()
            ->select(['id', 'order_id', 'seller_id', 'total_price'])
            ->with(['order:id,payment_status,status,order_total,created_at,updated_at'])
            ->whereBelongsTo($seller);

        if ($dateFrom || $dateTo || $status) {
            $query->whereHas('order', function ($orderQuery) use ($dateFrom, $dateTo, $status) {
                if ($dateFrom) {
                    $orderQuery->whereDate('created_at', '>=', Carbon::parse($dateFrom));
                }

                if ($dateTo) {
                    $orderQuery->whereDate('created_at', '<=', Carbon::parse($dateTo));
                }

                if ($status) {
                    $orderQuery->where('payment_status', $status);
                }
            });
        }

        $orderItems = $query->get();
        $orders = $orderItems
            ->groupBy('order_id')
            ->map(function (Collection $items): Order {
                $order = $items->firstOrFail()->order;
                $order->setAttribute('seller_total', $items->sum('total_price'));

                return $order;
            })
            ->values();
        $paidOrders = $orders->where('payment_status', Order::STATUS['PAID']);

        return [
            'all' => $orders,
            'pending' => $orders->where('payment_status', Order::STATUS['PENDING'])->count(),
            'processing' => $orders->where('payment_status', Order::STATUS['PROCESSING'])->count(),
            'shipped' => $orders->where('payment_status', Order::STATUS['SHIPPED'])->count(),
            'delivered' => $orders->where('payment_status', Order::STATUS['DELIVERED'])->count(),
            'cancelled' => $orders->where('payment_status', Order::STATUS['CANCELLED'])->count(),
            'refunded' => $orders->where('payment_status', Order::STATUS['REFUNDED'])->count(),
            'total' => $orders->count(),
            'totalAmount' => $orders->sum('seller_total'),
            'totalRevenue' => $paidOrders->sum('seller_total'),
            'averageOrderValue' => $paidOrders->isEmpty()
                ? 0
                : (float) $paidOrders->avg('seller_total'),
        ];
    }
}
