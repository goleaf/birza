<?php

namespace App\Livewire\Backend\Products;

use App\Livewire\Concerns\InteractsWithMaryTableSorting;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Category;
use App\Models\Product;
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

    #[Url(as: 'status', except: '')]
    public ?string $statusFilter = null;

    #[Url(as: 'category', except: '')]
    public ?string $categoryFilter = null;

    #[Url(as: 'seller', except: '')]
    public ?string $sellerFilter = null;

    #[Url(as: 'min_price', except: '')]
    public ?string $minPrice = null;

    #[Url(as: 'max_price', except: '')]
    public ?string $maxPrice = null;

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
        $this->sortBy = $this->sortByFromString($this->sort, ['created_at', 'price', 'name'], 'created_at');
        $this->sort = $this->sortString($this->sortBy);
    }

    public function confirmDeleteProduct(int $productId): void
    {
        $this->confirmDelete(method: 'deleteProduct', params: $productId);
    }

    public function confirmForceDeleteProduct(int $productId): void
    {
        $this->confirmDelete(method: 'forceDeleteProduct', params: $productId);
    }

    public function deleteProduct(int $productId): void
    {
        Product::query()->findOrFail($productId)->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function restoreProduct(int $productId): void
    {
        $product = Product::withTrashed()->findOrFail($productId);
        $product->restore();

        $this->notifySuccess(__('backend_common_restore_success'));
    }

    public function forceDeleteProduct(int $productId): void
    {
        $product = Product::withTrashed()->findOrFail($productId);
        $product->deleteStoredImages();
        $product->forceDelete();

        $this->notifySuccess(__('backend_common_force_delete_success'));
    }

    public function clear(): void
    {
        $this->reset('search', 'statusFilter', 'categoryFilter', 'sellerFilter', 'minPrice', 'maxPrice');
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
            $this->sortBy = $this->normalizeSortBy($this->sortBy, ['created_at', 'price', 'name'], 'created_at');
            $this->sort = $this->sortString($this->sortBy);
        }

        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'image', 'label' => __('product_image_2'), 'sortable' => false, 'class' => 'w-20'],
            ['key' => 'name', 'label' => __('product_name')],
            ['key' => 'category', 'label' => __('product_category'), 'sortable' => false],
            ['key' => 'seller', 'label' => __('product_seller'), 'sortable' => false],
            ['key' => 'price', 'label' => __('product_price'), 'class' => 'text-right'],
            ['key' => 'status', 'label' => __('common_status'), 'sortable' => false],
        ];
    }

    public function statusOptions(): array
    {
        return [
            ['id' => 'active', 'name' => __('common_active')],
            ['id' => 'trashed', 'name' => __('common_trashed')],
        ];
    }

    public function categoryOptions($categories): array
    {
        $options = [];

        foreach ($categories as $category) {
            $options[] = [
                'id' => (string) $category->id,
                'name' => $category->getTranslation('category_name', app()->getLocale()),
            ];

            foreach ($category->subcategories as $subcategory) {
                $options[] = [
                    'id' => (string) $subcategory->id,
                    'name' => '-- '.$subcategory->getTranslation('category_name', app()->getLocale()),
                ];
            }
        }

        return $options;
    }

    public function sellerOptions($sellers): array
    {
        return collect($sellers)
            ->map(fn (Seller $seller) => [
                'id' => (string) $seller->id,
                'name' => $seller->company_name ?: $seller->name,
            ])
            ->values()
            ->all();
    }

    public function render()
    {
        $query = Product::query()
            ->with(['category', 'seller'])
            ->when($this->statusFilter !== null && $this->statusFilter !== '', function ($query) {
                if ($this->statusFilter === 'trashed') {
                    $query->onlyTrashed();
                } elseif ($this->statusFilter === 'active') {
                    $query->whereNull('deleted_at');
                }
            })
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhereHas('category', function ($query) use ($search) {
                            $query->where('category_name->en', 'like', $search);
                        })
                        ->orWhereHas('seller', function ($query) use ($search) {
                            $query->where('name', 'like', $search)
                                ->orWhere('company_name', 'like', $search);
                        });
                });
            })
            ->when($this->categoryFilter !== null && $this->categoryFilter !== '', function ($query) {
                $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->sellerFilter !== null && $this->sellerFilter !== '', function ($query) {
                $query->where('seller_id', $this->sellerFilter);
            })
            ->when($this->minPrice !== null && $this->minPrice !== '', function ($query) {
                $query->where('price', '>=', $this->minPrice);
            })
            ->when($this->maxPrice !== null && $this->maxPrice !== '', function ($query) {
                $query->where('price', '<=', $this->maxPrice);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        $categories = Category::select('id', 'category_name', 'parent_category_id')
            ->whereNull('parent_category_id')
            ->with(['subcategories' => function ($query) {
                $query->select('id', 'category_name', 'parent_category_id')
                    ->orderBy('category_name->en');
            }])
            ->orderBy('category_name->en')
            ->get();

        $sellers = Seller::select('id', 'name', 'company_name')
            ->orderBy('name')
            ->get();

        return view('backend.products.index', [
            'categoryOptions' => $this->categoryOptions($categories),
            'headers' => $this->headers(),
            'products' => $query->paginate($this->perPage)->withQueryString(),
            'sellerOptions' => $this->sellerOptions($sellers),
            'statusOptions' => $this->statusOptions(),
        ]);
    }
}
