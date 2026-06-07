<?php

namespace App\Livewire\Backend\Sellers;

use App\Livewire\Concerns\InteractsWithMaryTableSorting;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Users\Seller;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithMaryTableSorting;
    use InteractsWithWireUi;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'is_active', except: '')]
    public ?string $activeFilter = null;

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
        $this->sortBy = $this->sortByFromString($this->sort, ['created_at', 'name', 'company_name'], 'created_at');
        $this->sort = $this->sortString($this->sortBy);
    }

    public function confirmDeleteSeller(int $sellerId): void
    {
        $this->confirmDelete(method: 'deleteSeller', params: $sellerId);
    }

    public function deleteSeller(int $sellerId): void
    {
        Seller::query()->findOrFail($sellerId)->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function clear(): void
    {
        $this->reset('search', 'activeFilter');
        $this->sortBy = [
            'column' => 'created_at',
            'direction' => 'desc',
        ];
        $this->sort = $this->sortString($this->sortBy);
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if ($property === 'drawer') {
            return;
        }

        if (str_starts_with($property, 'sortBy')) {
            $this->sortBy = $this->normalizeSortBy($this->sortBy, ['created_at', 'name', 'company_name'], 'created_at');
            $this->sort = $this->sortString($this->sortBy);
        }

        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => __('sellers_field_name')],
            ['key' => 'email', 'label' => __('sellers_field_email'), 'sortable' => false],
            ['key' => 'company_name', 'label' => __('sellers_field_company_name')],
            ['key' => 'active', 'label' => __('sellers_field_active_status'), 'sortable' => false],
        ];
    }

    public function activeOptions(): array
    {
        return [
            ['id' => 'true', 'name' => __('sellers_field_active')],
            ['id' => 'false', 'name' => __('sellers_field_inactive')],
        ];
    }

    public function render()
    {
        $query = Seller::query()
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('company_name', 'like', $search)
                        ->orWhere('vat_code', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->when($this->activeFilter !== null && $this->activeFilter !== '', function ($query) {
                $query->where('is_active', $this->activeFilter === 'true');
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return view('backend.sellers.index', [
            'headers' => $this->headers(),
            'activeOptions' => $this->activeOptions(),
            'sellers' => $query->paginate($this->perPage)->withQueryString(),
        ]);
    }
}
