<?php

namespace App\Livewire\Backend\Categories;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Category;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use InteractsWithWireUi;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'structure', except: '')]
    public ?string $structureFilter = null;

    #[Url(as: 'attributes', except: '')]
    public ?string $attributePresenceFilter = null;

    public bool $drawer = false;

    public int $perPage = 15;

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
    }

    public function confirmDeleteCategory(int $categoryId): void
    {
        $this->confirmDelete(method: 'deleteCategory', params: $categoryId);
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);

        $this->authorize('delete', $category);

        $category->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStructureFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAttributePresenceFilter(): void
    {
        $this->resetPage();
    }

    public function clear(): void
    {
        $this->reset('search', 'structureFilter', 'attributePresenceFilter');
        $this->drawer = false;
        $this->resetPage();
    }

    public function render()
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        $categories = Category::query()
            ->select(['id', 'parent_category_id', 'category_name', 'order'])
            ->with([
                'parent:id,category_name',
                'attributes' => function ($query) {
                    $query->select(['attributes.id', 'name', 'is_active']);
                },
            ])
            ->withCount('attributes')
            ->when($this->search !== '', function ($query) use ($locale, $fallbackLocale) {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search, $locale, $fallbackLocale) {
                    $builder->where("category_name->{$locale}", 'like', $search)
                        ->orWhereHas('parent', function ($parentQuery) use ($search, $locale, $fallbackLocale) {
                            $parentQuery->where("category_name->{$locale}", 'like', $search);

                            if ($fallbackLocale !== $locale) {
                                $parentQuery->orWhere("category_name->{$fallbackLocale}", 'like', $search);
                            }
                        })
                        ->orWhereHas('attributes', function ($attributeQuery) use ($search, $locale, $fallbackLocale) {
                            $attributeQuery->where("name->{$locale}", 'like', $search);

                            if ($fallbackLocale !== $locale) {
                                $attributeQuery->orWhere("name->{$fallbackLocale}", 'like', $search);
                            }
                        });

                    if ($fallbackLocale !== $locale) {
                        $builder->orWhere("category_name->{$fallbackLocale}", 'like', $search);
                    }
                });
            })
            ->when($this->structureFilter === 'root', function ($query) {
                $query->whereNull('parent_category_id');
            })
            ->when($this->structureFilter === 'child', function ($query) {
                $query->whereNotNull('parent_category_id');
            })
            ->when($this->attributePresenceFilter === 'with', function ($query) {
                $query->has('attributes');
            })
            ->when($this->attributePresenceFilter === 'without', function ($query) {
                $query->doesntHave('attributes');
            })
            ->orderBy('parent_category_id')
            ->orderBy('order')
            ->orderBy('id')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('backend.categories.index', [
            'attributePresenceOptions' => [
                ['id' => 'with', 'name' => __('backend_categories_filters_with_attributes')],
                ['id' => 'without', 'name' => __('backend_categories_filters_without_attributes')],
            ],
            'categories' => $categories,
            'structureOptions' => [
                ['id' => 'root', 'name' => __('backend_categories_filters_root')],
                ['id' => 'child', 'name' => __('backend_categories_filters_subcategory')],
            ],
        ]);
    }
}
