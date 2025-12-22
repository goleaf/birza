<?php

namespace App\Livewire\Backend\Products;

use App\Models\Country;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.backend.app')]
class Edit extends Component
{
    use WithFileUploads;

    public Product $product;

    public int $category_id;
    public float $price;
    public string $pack_type = '';
    public string $unit = '';
    public int $country_of_origin;
    public int $is_organic = 0;
    public int $is_active = 1;
    public int $stock = 0;

    public ?float $min_order_price = null;
    public ?int $min_order_count = null;
    public ?float $package_weight = null;
    public ?float $price_per_liter = null;

    public string $description = '';

    public $product_image = null;
    public $product_additional_image = null;

    public array $attributeSelections = [];

    public function mount(Product $product): void
    {
        $this->product = $product->load('attributeValues');

        $this->category_id = (int) $product->category_id;
        $this->price = (float) $product->price;
        $this->pack_type = (string) ($product->pack_type ?? '');
        $this->unit = (string) ($product->unit ?? '');
        $this->country_of_origin = (int) $product->country_of_origin;
        $this->is_organic = (int) $product->is_organic;
        $this->is_active = (int) $product->is_active;
        $this->stock = (int) $product->stock;

        $this->min_order_price = $product->min_order_price !== null ? (float) $product->min_order_price : null;
        $this->min_order_count = $product->min_order_count !== null ? (int) $product->min_order_count : null;
        $this->package_weight = $product->package_weight !== null ? (float) $product->package_weight : null;
        $this->price_per_liter = $product->price_per_liter !== null ? (float) $product->price_per_liter : null;

        $this->description = (string) ($product->getTranslation('description', app()->getLocale()) ?? '');

        $this->attributeSelections = $product->attributeValues
            ->mapWithKeys(fn ($value) => [$value->attribute_id => $value->id])
            ->all();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'price' => ['required', 'numeric', 'min:0'],
            'pack_type' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', Rule::in(Product::UNITS)],
            'country_of_origin' => ['required', 'integer', Rule::exists('countries', 'id')],
            'is_organic' => ['required', Rule::in([0, 1, '0', '1'])],
            'is_active' => ['required', Rule::in([0, 1, '0', '1'])],
            'stock' => ['required', 'integer', 'min:0'],
            'min_order_price' => ['nullable', 'numeric', 'min:0'],
            'min_order_count' => ['nullable', 'integer', 'min:1'],
            'package_weight' => ['nullable', 'numeric', 'min:0'],
            'price_per_liter' => ['nullable', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:15048'],
            'product_additional_image' => ['nullable', 'image', 'max:2048'],
            'attributeSelections' => ['nullable', 'array'],
            'attributeSelections.*' => ['nullable', 'integer', Rule::exists('attribute_values', 'id')],
        ]);

        $this->product->fill([
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'pack_type' => $validated['pack_type'],
            'unit' => $validated['unit'],
            'country_of_origin' => $validated['country_of_origin'],
            'is_organic' => (int) $validated['is_organic'] === 1,
            'is_active' => (int) $validated['is_active'] === 1,
            'stock' => $validated['stock'],
            'min_order_price' => $validated['min_order_price'] ?? null,
            'min_order_count' => $validated['min_order_count'] ?? null,
            'package_weight' => $validated['package_weight'] ?? null,
            'price_per_liter' => $validated['price_per_liter'] ?? null,
        ]);

        $this->product->setTranslation('description', app()->getLocale(), $validated['description']);

        if ($this->product_image) {
            $this->product->product_image = $this->storeProductImage($this->product_image, $this->product->product_image);
        }

        if ($this->product_additional_image) {
            $this->product->product_additional_image = $this->storeProductImage($this->product_additional_image, $this->product->product_additional_image);
        }

        $this->product->save();

        $sync = [];
        foreach (($validated['attributeSelections'] ?? []) as $attributeId => $valueId) {
            if (! $valueId) {
                continue;
            }
            $sync[(int) $valueId] = ['attribute_id' => (int) $attributeId];
        }

        $this->product->attributeValues()->sync($sync);

        session()->flash('success', __('messages.product_updated'));
        $this->redirectRoute('backend.products.index');
    }

    private function storeProductImage($imageFile, ?string $oldImage = null): string
    {
        if ($oldImage) {
            Storage::disk('public')->delete('products/' . $oldImage);
        }

        $image = Image::make($imageFile)
            ->resize(500, 500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80);

        $filename = uniqid() . '.webp';
        Storage::disk('public')->put('products/' . $filename, (string) $image);

        return $filename;
    }

    public function render()
    {
        $categories = Category::all();
        $countries = Country::active()->orderBy('alpha2')->get();

        $attributes = $this->product->category->attributes()
            ->select('attributes.id', 'attributes.name', 'attributes.type', 'attributes.is_required')
            ->with(['values' => function ($query) {
                $query->select('id', 'attribute_id', 'value')
                    ->where('is_active', true);
            }])
            ->with(['values.products' => function ($query) {
                $query->where('products.id', $this->product->id)
                    ->select('products.id');
            }])
            ->active()
            ->get();

        return view('backend.products.form', [
            'product' => $this->product,
            'categories' => $categories,
            'category' => $this->product->category,
            'countries' => $countries,
            'attributes' => $attributes,
        ]);
    }
}


