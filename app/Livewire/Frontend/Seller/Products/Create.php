<?php

namespace App\Livewire\Frontend\Seller\Products;

use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.frontend.app')]
class Create extends Component
{
    use WithFileUploads;

    public Category $selectedCategory;

    public int $category_id;
    public string $name = '';
    public ?float $price = null;
    public string $pack_type = '';
    public string $unit = '';
    public ?int $country_of_origin = null;
    public int $is_organic = 0;
    public int $is_active = 1;
    public ?float $min_order_price = null;
    public int $min_order_count = 1;
    public int $stock = 1;
    public array $description = [];
    public ?int $temperature_conditions_from = null;
    public ?int $temperature_conditions_to = null;
    public ?string $use_until = null;
    public ?int $total_shelf_life = null;

    public $product_image = null;
    public $product_additional_image = null;

    public function mount(Category $categoryId): void
    {
        $this->selectedCategory = $categoryId->load('subcategories');

        $this->category_id = $this->selectedCategory->id;
        $this->unit = collect(Product::UNITS)->sort()->first() ?? 'kg';

        foreach (config('app.locales') as $locale) {
            $this->description[$locale] = '';
        }
    }

    public function save(): void
    {
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'pack_type' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(Product::UNITS)],
            'country_of_origin' => ['required', 'integer', Rule::exists('countries', 'id')],
            'is_organic' => ['required', Rule::in([0, 1, '0', '1'])],
            'is_active' => ['required', Rule::in([0, 1, '0', '1'])],
            'min_order_price' => ['nullable', 'numeric', 'min:0'],
            'min_order_count' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:1'],
            'temperature_conditions_from' => ['nullable', 'integer'],
            'temperature_conditions_to' => ['nullable', 'integer'],
            'use_until' => ['nullable', 'date'],
            'total_shelf_life' => ['required', 'integer'],
            'product_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:15048'],
            'product_additional_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:15048'],
        ];

        $currentLocale = app()->getLocale();
        foreach (config('app.locales') as $locale) {
            $rules["description.$locale"] = $locale === $currentLocale
                ? ['required', 'string']
                : ['nullable', 'string'];
        }

        $validated = $this->validate($rules);

        $sellerId = Auth::guard('seller')->id();

        $product = new Product();
        $product->fill([
            'category_id' => $validated['category_id'],
            'seller_id' => $sellerId,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'pack_type' => $validated['pack_type'],
            'unit' => $validated['unit'],
            'country_of_origin' => $validated['country_of_origin'],
            'is_organic' => (int) $validated['is_organic'] === 1,
            'is_active' => (int) $validated['is_active'] === 1,
            'min_order_price' => $validated['min_order_price'] ?? null,
            'min_order_count' => $validated['min_order_count'],
            'stock' => $validated['stock'],
            'temperature_conditions_from' => $validated['temperature_conditions_from'] ?? null,
            'temperature_conditions_to' => $validated['temperature_conditions_to'] ?? null,
            'use_until' => $validated['use_until'] ?? null,
            'total_shelf_life' => $validated['total_shelf_life'],
        ]);

        $product->setTranslations('description', $validated['description']);

        $product->product_image = $this->storeProductImage($this->product_image);

        if ($this->product_additional_image) {
            $product->product_additional_image = $this->storeProductImage($this->product_additional_image);
        }

        $product->save();

        session()->flash('success', __('messages.product_created'));
        $this->redirectRoute('seller.products.index', navigate: true);
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
        $countries = $this->getEuropeanCountries();

        return view('frontend.seller.products.form', [
            'product' => null,
            'selectedCategory' => $this->selectedCategory,
            'countries' => $countries,
            'subcategories' => $this->selectedCategory->subcategories,
        ]);
    }

    private function getEuropeanCountries()
    {
        return Country::active()
            ->where('region', 'Europe')
            ->orderBy('alpha2')
            ->get();
    }
}


