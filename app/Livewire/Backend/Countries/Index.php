<?php

namespace App\Livewire\Backend\Countries;

use App\Livewire\Concerns\InteractsWithMaryTableSorting;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Country;
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

    #[Url(as: 'region', except: '')]
    public ?string $regionFilter = null;

    #[Url(as: 'status', except: '')]
    public ?string $activeFilter = null;

    #[Url(except: 'alpha2,asc')]
    public string $sort = 'alpha2,asc';

    public bool $drawer = false;

    public int $perPage = 15;

    /**
     * @var array{column: string, direction: string}
     */
    public array $sortBy = [
        'column' => 'alpha2',
        'direction' => 'asc',
    ];

    public function mount(): void
    {
        $this->sortBy = $this->sortByFromString($this->sort, ['alpha2', 'region'], 'alpha2', 'asc');
        $this->sort = $this->sortString($this->sortBy);
    }

    public function confirmDeleteCountry(int $countryId): void
    {
        $this->confirmDelete(method: 'deleteCountry', params: $countryId);
    }

    public function deleteCountry(int $countryId): void
    {
        Country::query()->findOrFail($countryId)->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function clear(): void
    {
        $this->reset('search', 'regionFilter', 'activeFilter');
        $this->sortBy = [
            'column' => 'alpha2',
            'direction' => 'asc',
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
            $this->sortBy = $this->normalizeSortBy($this->sortBy, ['alpha2', 'region'], 'alpha2', 'asc');
            $this->sort = $this->sortString($this->sortBy);
        }

        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'alpha2', 'label' => __('backend_countries_fields_code')],
            ['key' => 'region', 'label' => __('backend_countries_fields_region')],
            ['key' => 'country_name', 'label' => __('backend_countries_fields_country_name'), 'sortable' => false],
            ['key' => 'active', 'label' => __('common_status'), 'sortable' => false],
        ];
    }

    public function regionOptions(): array
    {
        return collect(Country::getRegionValues())
            ->map(fn (string $region) => [
                'id' => $region,
                'name' => __('backend.countries.regions.'.strtolower($region)),
            ])
            ->values()
            ->all();
    }

    public function activeOptions(): array
    {
        return [
            ['id' => 'true', 'name' => __('common_active')],
            ['id' => 'false', 'name' => __('common_inactive')],
        ];
    }

    public function render()
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        $query = Country::query()
            ->select(['id', 'alpha2', 'region', 'is_active', 'country_name'])
            ->when($this->search !== '', function ($query) use ($locale, $fallbackLocale) {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search, $locale, $fallbackLocale) {
                    $builder->where('alpha2', 'like', $search)
                        ->orWhere("country_name->{$locale}", 'like', $search);

                    if ($fallbackLocale !== $locale) {
                        $builder->orWhere("country_name->{$fallbackLocale}", 'like', $search);
                    }
                });
            })
            ->when($this->regionFilter !== null && $this->regionFilter !== '', function ($query) {
                $query->where('region', $this->regionFilter);
            })
            ->when($this->activeFilter !== null && $this->activeFilter !== '', function ($query) {
                $query->where('is_active', $this->activeFilter === 'true');
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return view('backend.countries.index', [
            'activeOptions' => $this->activeOptions(),
            'countries' => $query->paginate($this->perPage)->withQueryString(),
            'headers' => $this->headers(),
            'regionOptions' => $this->regionOptions(),
        ]);
    }
}
