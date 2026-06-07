<?php

namespace App\Livewire\Backend\ProductReports;

use App\Enums\ProductReportReason;
use App\Enums\ProductReportStatus;
use App\Livewire\Concerns\InteractsWithMaryTableSorting;
use App\Models\ProductReport;
use App\Models\Users\Seller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use InteractsWithMaryTableSorting;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public ?string $statusFilter = null;

    #[Url(as: 'reason', except: '')]
    public ?string $reasonFilter = null;

    #[Url(as: 'seller', except: '')]
    public ?string $sellerFilter = null;

    #[Url(except: 'created_at,desc')]
    public string $sort = 'created_at,desc';

    public bool $drawer = false;

    public int $perPage = 15;

    /**
     * @var array{column: string, direction: string}
     */
    public array $sortBy = [
        'column' => 'created_at',
        'direction' => 'desc',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', ProductReport::class);

        $this->sortBy = $this->sortByFromString($this->sort, ['created_at', 'status', 'reason'], 'created_at');
        $this->sort = $this->sortString($this->sortBy);
    }

    public function updated(string $property): void
    {
        if ($property === 'drawer') {
            return;
        }

        if (str_starts_with($property, 'sortBy')) {
            $this->sortBy = $this->normalizeSortBy($this->sortBy, ['created_at', 'status', 'reason'], 'created_at');
            $this->sort = $this->sortString($this->sortBy);
        }

        $this->resetPage();
    }

    public function clear(): void
    {
        $this->reset('search', 'statusFilter', 'reasonFilter', 'sellerFilter');
        $this->sortBy = [
            'column' => 'created_at',
            'direction' => 'desc',
        ];
        $this->sort = $this->sortString($this->sortBy);
        $this->perPage = 15;
        $this->resetPage();
    }

    /**
     * @return list<array{key: string, label: string, class?: string, sortable?: bool}>
     */
    public function headers(): array
    {
        return [
            ['key' => 'created_at', 'label' => __('admin.reports.columns.created_at')],
            ['key' => 'product', 'label' => __('admin.reports.columns.product'), 'sortable' => false],
            ['key' => 'seller', 'label' => __('admin.reports.columns.seller'), 'sortable' => false],
            ['key' => 'reason', 'label' => __('admin.reports.columns.reason')],
            ['key' => 'status', 'label' => __('admin.reports.columns.status')],
            ['key' => 'reporter', 'label' => __('admin.reports.columns.reporter'), 'sortable' => false],
        ];
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function sellerOptions(): array
    {
        return Seller::query()
            ->whereHas('products.reports')
            ->orderBy('company_name')
            ->orderBy('name')
            ->get(['id', 'company_name', 'name'])
            ->map(fn (Seller $seller): array => [
                'id' => (string) $seller->id,
                'name' => $seller->company_name ?: $seller->name,
            ])
            ->values()
            ->all();
    }

    public function render()
    {
        $sortBy = $this->normalizeSortBy($this->sortBy, ['created_at', 'status', 'reason'], 'created_at');

        $reports = ProductReport::query()
            ->select([
                'id',
                'product_id',
                'reporter_id',
                'reporter_email',
                'reason',
                'status',
                'reviewed_by',
                'reviewed_at',
                'created_at',
            ])
            ->with([
                'product:id,name,seller_id,is_active,deleted_at',
                'product.seller:id,name,company_name,email',
                'reporter:id,name,email',
                'reviewedBy:id,name,email',
            ])
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->reasonFilter, fn ($query) => $query->where('reason', $this->reasonFilter))
            ->when($this->sellerFilter, function ($query): void {
                $query->whereHas('product', fn ($productQuery) => $productQuery->where('seller_id', (int) $this->sellerFilter));
            })
            ->when($this->search !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery
                        ->whereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'))
                        ->orWhere('reporter_email', 'like', '%'.$search.'%');

                    if (is_numeric($search)) {
                        $nestedQuery->orWhere('product_id', (int) $search);
                    }
                });
            })
            ->orderBy($sortBy['column'], $sortBy['direction'])
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.backend.product-reports.index', [
            'reports' => $reports,
            'headers' => $this->headers(),
            'statusOptions' => ProductReportStatus::options(),
            'reasonOptions' => ProductReportReason::options(),
            'sellerOptions' => $this->sellerOptions(),
        ]);
    }
}
