<?php

namespace App\Livewire\Backend\Attributes\Values;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Attribute;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithWireUi;
    use WithPagination;

    public Attribute $attribute;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public ?string $activeFilter = null;

    public bool $drawer = false;

    public int $perPage = 15;

    public function mount(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function confirmDeleteValue(int $valueId): void
    {
        $this->confirmDelete(method: 'deleteValue', params: $valueId);
    }

    public function deleteValue(int $valueId): void
    {
        $value = $this->attribute->values()->findOrFail($valueId);
        $value->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function clear(): void
    {
        $this->reset('search', 'activeFilter');
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if ($property === 'drawer') {
            return;
        }

        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'value', 'label' => __('backend_attribute_values_fields_value'), 'sortable' => false],
            ['key' => 'active', 'label' => __('backend_attribute_values_fields_is_active'), 'sortable' => false],
        ];
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

        $values = $this->attribute->values()
            ->select(['id', 'attribute_id', 'value', 'is_active'])
            ->when($this->search !== '', function ($query) use ($locale, $fallbackLocale) {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search, $locale, $fallbackLocale) {
                    $builder->where("value->{$locale}", 'like', $search);

                    if ($fallbackLocale !== $locale) {
                        $builder->orWhere("value->{$fallbackLocale}", 'like', $search);
                    }
                });
            })
            ->when($this->activeFilter !== null && $this->activeFilter !== '', function ($query) {
                $query->where('is_active', $this->activeFilter === 'true');
            })
            ->orderBy('id')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('backend.attributes.values.index', [
            'activeOptions' => $this->activeOptions(),
            'attribute' => $this->attribute,
            'headers' => $this->headers(),
            'values' => $values,
        ]);
    }
}
