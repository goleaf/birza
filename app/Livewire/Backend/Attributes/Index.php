<?php

namespace App\Livewire\Backend\Attributes;

use App\Livewire\Concerns\InteractsWithMaryTableSorting;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Attribute;
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

    #[Url(as: 'status', except: '')]
    public ?string $statusFilter = null;

    #[Url(as: 'type', except: '')]
    public ?string $typeFilter = null;

    #[Url(as: 'filterable', except: '')]
    public ?string $filterableFilter = null;

    #[Url(as: 'required', except: '')]
    public ?string $requiredFilter = null;

    #[Url(except: 'id,desc')]
    public string $sort = 'id,desc';

    public bool $drawer = false;

    public int $perPage = 20;

    /**
     * @var array{column: string, direction: string}
     */
    public array $sortBy = [
        'column' => 'id',
        'direction' => 'desc',
    ];

    public function mount(): void
    {
        $this->sortBy = $this->sortByFromString($this->sort, ['id', 'type', 'values_count'], 'id');
        $this->sort = $this->sortString($this->sortBy);
    }

    public function confirmDeleteAttribute(int $attributeId): void
    {
        $this->confirmDelete(method: 'deleteAttribute', params: $attributeId);
    }

    public function deleteAttribute(int $attributeId): void
    {
        Attribute::query()->findOrFail($attributeId)->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function clear(): void
    {
        $this->reset('search', 'statusFilter', 'typeFilter', 'filterableFilter', 'requiredFilter');
        $this->sortBy = [
            'column' => 'id',
            'direction' => 'desc',
        ];
        $this->sort = $this->sortString($this->sortBy);
        $this->perPage = 20;
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if ($property === 'drawer') {
            return;
        }

        if (str_starts_with($property, 'sortBy')) {
            $this->sortBy = $this->normalizeSortBy($this->sortBy, ['id', 'type', 'values_count'], 'id');
            $this->sort = $this->sortString($this->sortBy);
        }

        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => __('backend_attributes_fields_name'), 'sortable' => false],
            ['key' => 'type', 'label' => __('backend_attributes_fields_type')],
            ['key' => 'values_count', 'label' => __('backend_attributes_fields_values_count')],
            ['key' => 'active', 'label' => __('backend_attributes_fields_status'), 'sortable' => false],
            ['key' => 'filterable', 'label' => __('backend_attributes_fields_is_filterable'), 'sortable' => false],
            ['key' => 'required', 'label' => __('backend_attributes_fields_is_required'), 'sortable' => false],
        ];
    }

    public function statusOptions(): array
    {
        return [
            ['id' => 'active', 'name' => __('common_active')],
            ['id' => 'inactive', 'name' => __('common_inactive')],
        ];
    }

    public function typeOptions(): array
    {
        return collect(Attribute::TYPES)
            ->map(fn (string $label, string $type) => [
                'id' => $type,
                'name' => __('backend_attributes_types_'.$type),
            ])
            ->values()
            ->all();
    }

    public function yesNoOptions(): array
    {
        return [
            ['id' => '1', 'name' => __('common_yes')],
            ['id' => '0', 'name' => __('common_no')],
        ];
    }

    public function render()
    {
        $query = Attribute::query()
            ->withCount('values');

        if ($this->search !== '') {
            $locale = app()->getLocale();
            $query->where("name->{$locale}", 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if (is_string($this->typeFilter) && array_key_exists($this->typeFilter, Attribute::TYPES)) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->filterableFilter === '1') {
            $query->where('is_filterable', true);
        } elseif ($this->filterableFilter === '0') {
            $query->where('is_filterable', false);
        }

        if ($this->requiredFilter === '1') {
            $query->where('is_required', true);
        } elseif ($this->requiredFilter === '0') {
            $query->where('is_required', false);
        }

        return view('backend.attributes.index', [
            'attributeRecords' => $query
                ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
                ->paginate($this->perPage)
                ->withQueryString(),
            'filterableOptions' => $this->yesNoOptions(),
            'headers' => $this->headers(),
            'requiredOptions' => $this->yesNoOptions(),
            'stats' => [
                'total' => Attribute::count(),
                'active' => Attribute::where('is_active', true)->count(),
                'filterable' => Attribute::where('is_filterable', true)->count(),
                'required' => Attribute::where('is_required', true)->count(),
            ],
            'statusOptions' => $this->statusOptions(),
            'typeOptions' => $this->typeOptions(),
        ]);
    }
}
