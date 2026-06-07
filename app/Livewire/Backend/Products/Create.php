<?php

namespace App\Livewire\Backend\Products;

use App\Livewire\Concerns\InteractsWithProductImageLibrary;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.backend.app')]
class Create extends Component
{
    use InteractsWithProductImageLibrary;
    use WithFileUploads;

    public ?int $category_id = null;

    public ?int $seller_id = null;

    public string $name = '';

    public float $price = 0.0;

    public string $pack_type = '';

    public string $unit = '';

    public ?int $country_of_origin = null;

    public bool $is_organic = false;

    public bool $is_active = true;

    public int $stock = 0;

    public ?float $min_order_price = null;

    public ?int $min_order_count = null;

    public ?int $temperature_conditions_from = null;

    public ?int $temperature_conditions_to = null;

    public ?string $use_until = null;

    public ?int $total_shelf_life = null;

    public ?float $package_weight = null;

    public ?float $price_per_liter = null;

    public string $description = '';

    public array $attributeSelections = [];

    public function mount(): void
    {
        $this->unit = Product::defaultUnit();
        $this->initializeProductImageLibrary();
    }

    public function updatedCategoryId(): void
    {
        $this->attributeSelections = [];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return array_merge([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'seller_id' => ['required', 'integer', Rule::exists('users_sellers', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'pack_type' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', Rule::in(Product::unitValues())],
            'country_of_origin' => ['required', 'integer', Rule::exists('countries', 'id')],
            'is_organic' => ['boolean'],
            'is_active' => ['boolean'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_order_price' => ['nullable', 'numeric', 'min:0'],
            'min_order_count' => ['required', 'integer', 'min:1'],
            'temperature_conditions_from' => ['nullable', 'integer'],
            'temperature_conditions_to' => ['nullable', 'integer'],
            'use_until' => ['nullable', 'date'],
            'total_shelf_life' => ['required', 'integer'],
            'package_weight' => ['nullable', 'numeric', 'min:0'],
            'price_per_liter' => ['nullable', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
            'attributeSelections' => ['nullable', 'array'],
            'attributeSelections.*' => ['nullable', 'integer', Rule::exists('attribute_values', 'id')],
        ], $this->productImageLibraryRules());
    }

    public function save(): void
    {
        $this->ensureProductImageLibraryIsPresent();

        $validated = $this->validate();

        $product = new Product;
        $product->fill([
            'category_id' => $validated['category_id'],
            'seller_id' => $validated['seller_id'],
            'name' => $validated['name'],
            'price' => $validated['price'],
            'pack_type' => $validated['pack_type'],
            'unit' => $validated['unit'],
            'country_of_origin' => $validated['country_of_origin'],
            'is_organic' => (bool) ($validated['is_organic'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'stock' => $validated['stock'],
            'min_order_price' => $validated['min_order_price'] ?? null,
            'min_order_count' => $validated['min_order_count'] ?? null,
            'temperature_conditions_from' => $validated['temperature_conditions_from'] ?? null,
            'temperature_conditions_to' => $validated['temperature_conditions_to'] ?? null,
            'use_until' => $validated['use_until'] ?? null,
            'total_shelf_life' => $validated['total_shelf_life'] ?? null,
            'package_weight' => $validated['package_weight'] ?? null,
            'price_per_liter' => $validated['price_per_liter'] ?? null,
            'product_image' => '',
            'product_additional_image' => null,
        ]);

        $product->setTranslation('description', app()->getLocale(), $validated['description']);
        $product->save();
        $this->syncProductImageLibrary($product);

        $sync = [];
        foreach (($validated['attributeSelections'] ?? []) as $attributeId => $valueId) {
            if (! $valueId) {
                continue;
            }

            $sync[(int) $valueId] = ['attribute_id' => (int) $attributeId];
        }

        $product->attributeValues()->sync($sync);

        session()->flash('success', __('messages_product_created'));
        $this->redirectRoute('backend.products.index');
    }

    public function render()
    {
        $categories = Category::query()
            ->select(['id', 'category_name', 'parent_category_id'])
            ->whereNull('parent_category_id')
            ->with(['subcategories' => function ($query) {
                $query->select(['id', 'category_name', 'parent_category_id'])
                    ->orderBy('category_name->en');
            }])
            ->orderBy('category_name->en')
            ->get();

        $countries = Country::active()
            ->select('id', 'country_name', 'alpha2')
            ->orderBy('alpha2')
            ->get();

        $sellers = Seller::query()
            ->select(['id', 'name', 'email', 'company_name'])
            ->orderBy('company_name')
            ->orderBy('name')
            ->get();

        $productAttributes = $this->category_id
            ? Category::query()
                ->whereKey($this->category_id)
                ->with(['attributes' => function ($query) {
                    $query->select('attributes.id', 'attributes.name', 'attributes.type', 'attributes.is_required')
                        ->with(['values' => function ($valueQuery) {
                            $valueQuery->select(['id', 'attribute_id', 'value'])
                                ->where('is_active', true);
                        }])
                        ->active();
                }])
                ->first()?->attributes ?? collect()
            : collect();

        return view('backend.products.form', [
            'categoryOptions' => $categories->flatMap(function (Category $category) {
                $options = [[
                    'id' => $category->id,
                    'name' => $category->getTranslation('category_name', app()->getLocale()),
                ]];

                foreach ($category->subcategories as $subcategory) {
                    $options[] = [
                        'id' => $subcategory->id,
                        'name' => '-- '.$subcategory->getTranslation('category_name', app()->getLocale()),
                    ];
                }

                return $options;
            })
                ->values()
                ->all(),
            'countryOptions' => $countries->map(fn (Country $country) => [
                'id' => $country->id,
                'name' => $country->getTranslation('country_name', app()->getLocale()),
                'sub_label' => strtoupper((string) $country->alpha2),
            ])
                ->values()
                ->all(),
            'productAttributes' => $productAttributes,
            'sellerOptions' => $sellers->map(fn (Seller $seller) => [
                'id' => $seller->id,
                'name' => $seller->company_name ?: $seller->name,
                'sub_label' => collect([$seller->name, $seller->email])
                    ->filter()
                    ->join(' • '),
            ])
                ->values()
                ->all(),
            'unitOptions' => Product::unitOptions(),
        ]);
    }
}
