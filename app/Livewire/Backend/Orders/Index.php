<?php

namespace App\Livewire\Backend\Orders;

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
            ['key' => 'payment_status', 'label' => __('orders_status_3'), 'sortable' => false],
            ['key' => 'order_total', 'label' => __('orders_table_amount')],
        ];
    }

    public function statusOptions(): array
    {
        return collect(Order::STATUS)
            ->map(fn (string $status) => [
                'id' => $status,
                'name' => __('orders_status_3_'.strtolower($status)),
            ])
            ->values()
            ->all();
    }

    public function render()
    {
        $query = Order::withFullDetails();

        if ($this->statusFilter !== null && $this->statusFilter !== '') {
            $query->where('payment_status', $this->statusFilter);
        }

        if ($this->dateFrom !== null && $this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== null && $this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $orders = $query
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage)
            ->withQueryString();

        return view('backend.orders.index', [
            'orders' => $orders,
            'headers' => $this->headers(),
            'statusOptions' => $this->statusOptions(),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('payment_status', Order::STATUS['PENDING'])->count(),
            'averageOrderValue' => Order::where('payment_status', Order::STATUS['PAID'])->avg('order_total') ?? 0,
            'totalRevenue' => Order::where('payment_status', Order::STATUS['PAID'])->sum('order_total') ?? 0,
        ]);
    }
}
