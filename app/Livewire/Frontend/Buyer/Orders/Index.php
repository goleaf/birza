<?php

namespace App\Livewire\Frontend\Buyer\Orders;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Users\Buyer;
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

    public function cancelOrder(int $orderId): void
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $order = Order::with(['items.product'])
            ->where('buyer_id', $buyer->id)
            ->findOrFail($orderId);

        if (! $order->canBeCancelled()) {
            session()->flash('error', __('orders.messages.cannot_cancel'));

            return;
        }

        app(ChangeOrderStatusAction::class)->handle($order, OrderStatus::Cancelled, $buyer);

        session()->flash('success', __('orders.messages.cancelled_successfully'));
    }

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

    public function render()
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $filters = [
            'status' => $this->status,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ];

        $ordersData = $this->getOrdersData($buyer, $this->status, $this->dateFrom, $this->dateTo);

        return view('frontend.buyer.orders.index', [
            'ordersData' => $ordersData,
            'filters' => $filters,
            'orderStatuses' => OrderStatus::cases(),
            'buyer' => $buyer,
            'pendingStatus' => OrderStatus::Pending,
            'deliveredStatus' => OrderStatus::Delivered,
            'cancelledStatus' => OrderStatus::Cancelled,
            'orderCalendarEvents' => $ordersData['calendarEvents'],
        ]);
    }

    protected function getOrdersData(Buyer $buyer, $status = null, $dateFrom = null, $dateTo = null): array
    {
        $status = is_string($status) ? OrderStatus::tryFrom($status) : null;
        $query = $this->filteredOrdersQuery($buyer, $status, $dateFrom, $dateTo);

        $orders = (clone $query)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        $calendarOrders = (clone $query)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return [
            'all' => $orders,
            'counts' => $this->statusCounts((clone $query)),
            'total' => (clone $query)->count(),
            'totalSpent' => (clone $query)
                ->where('payment_status', OrderPaymentStatus::Paid->value)
                ->sum('order_total'),
            'calendarEvents' => Order::calendarEventsFrom($calendarOrders),
        ];
    }

    protected function filteredOrdersQuery(
        Buyer $buyer,
        ?OrderStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): Builder {
        $query = Order::query()
            ->summaryColumns()
            ->forBuyer($buyer)
            ->placedBetween($dateFrom, $dateTo);

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        return $query;
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
