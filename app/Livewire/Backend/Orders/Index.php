<?php

namespace App\Livewire\Backend\Orders;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Livewire\Concerns\InteractsWithMaryTableSorting;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithMaryTableSorting;
    use WithPagination;

    #[Url(as: 'status', except: '')]
    public ?string $statusFilter = null;

    #[Url(as: 'date_from', except: '')]
    public ?string $dateFrom = null;

    #[Url(as: 'date_to', except: '')]
    public ?string $dateTo = null;

    #[Url(except: 'created_at,desc')]
    public string $sort = 'created_at,desc';

    public bool $drawer = false;

    public int $perPage = 10;

    /**
     * @var array{column: string, direction: string}
     */
    public array $sortBy = [
        'column' => 'created_at',
        'direction' => 'desc',
    ];

    public function mount(): void
    {
        $this->sortBy = $this->sortByFromString($this->sort, ['created_at', 'order_total', 'id'], 'created_at');
        $this->sort = $this->sortString($this->sortBy);
    }

    public function clear(): void
    {
        $this->reset('statusFilter', 'dateFrom', 'dateTo');
        $this->sortBy = [
            'column' => 'created_at',
            'direction' => 'desc',
        ];
        $this->sort = $this->sortString($this->sortBy);
        $this->perPage = 10;
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if ($property === 'drawer') {
            return;
        }

        if (str_starts_with($property, 'sortBy')) {
            $this->sortBy = $this->normalizeSortBy($this->sortBy, ['created_at', 'order_total', 'id'], 'created_at');
            $this->sort = $this->sortString($this->sortBy);
        }

        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => __('orders_table_order_id'), 'class' => 'w-24'],
            ['key' => 'created_at', 'label' => __('orders_table_date')],
            ['key' => 'buyer', 'label' => __('buyer_buyer'), 'sortable' => false],
            ['key' => 'status', 'label' => __('orders.status.label'), 'sortable' => false],
            ['key' => 'order_total', 'label' => __('orders_table_amount')],
        ];
    }

    public function statusOptions(): array
    {
        return OrderStatus::options();
    }

    public function render()
    {
        $query = Order::query()
            ->summaryColumns()
            ->with('buyer:id,name,email');

        $statusFilter = $this->statusFilter !== null && $this->statusFilter !== ''
            ? OrderStatus::tryFrom($this->statusFilter)
            : null;

        if ($statusFilter !== null) {
            $query->where('status', $statusFilter->value);
        }

        $query->placedBetween($this->dateFrom, $this->dateTo);

        $orders = $query
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage)
            ->withQueryString();

        return view('backend.orders.index', [
            'orders' => $orders,
            'headers' => $this->headers(),
            'statusOptions' => $this->statusOptions(),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::pending()->count(),
            'averageOrderValue' => Order::where('payment_status', OrderPaymentStatus::Paid->value)->avg('order_total') ?? 0,
            'totalRevenue' => Order::where('payment_status', OrderPaymentStatus::Paid->value)->sum('order_total') ?? 0,
        ]);
    }
}
