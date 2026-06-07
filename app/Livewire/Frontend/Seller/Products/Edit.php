<?php

namespace App\Livewire\Frontend\Seller\Products;

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
class Edit extends Component
{
    use WithFileUploads;

    public Product $product;

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

    public function mount(Product $product): void
    {
        if ($product->seller_id !== Auth::guard('seller')->id()) {
            abort(403);
        }

        $this->product = $product;

        $this->category_id = (int) $product->category_id;
        $this->name = (string) ($product->name ?? '');
        $this->price = $product->price !== null ? (float) $product->price : null;
        $this->pack_type = (string) ($product->pack_type ?? '');
        $this->unit = (string) ($product->unit ?? (collect(Product::UNITS)->sort()->first() ?? 'kg'));
        $this->country_of_origin = $product->country_of_origin ? (int) $product->country_of_origin : null;
        $this->is_organic = (int) $product->is_organic;
        $this->is_active = (int) $product->is_active;
        $this->min_order_price = $product->min_order_price !== null ? (float) $product->min_order_price : null;
        $this->min_order_count = (int) ($product->min_order_count ?? 1);
        $this->stock = (int) ($product->stock ?? 1);
        $this->temperature_conditions_from = $product->temperature_conditions_from !== null ? (int) $product->temperature_conditions_from : null;
        $this->temperature_conditions_to = $product->temperature_conditions_to !== null ? (int) $product->temperature_conditions_to : null;
        $this->use_until = $product->use_until ? $product->use_until->format('Y-m-d') : null;
        $this->total_shelf_life = $product->total_shelf_life !== null ? (int) $product->total_shelf_life : null;

        foreach (config('app.locales') as $locale) {
            $this->description[$locale] = (string) ($product->getTranslation('description', $locale) ?? '');
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
            'product_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:15048'],
            'product_additional_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:15048'],
        ];

        $currentLocale = app()->getLocale();
        foreach (config('app.locales') as $locale) {
            $rules["description.$locale"] = $locale === $currentLocale
                ? ['required', 'string']
                : ['nullable', 'string'];
        }

        $validated = $this->validate($rules);

        $this->product->fill([
            'category_id' => $validated['category_id'],
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

        $this->product->setTranslations('description', $validated['description']);

        if ($this->product_image) {
            $this->product->product_image = $this->storeProductImage($this->product_image, $this->product->product_image);
        }

        if ($this->product_additional_image) {
            $this->product->product_additional_image = $this->storeProductImage($this->product_additional_image, $this->product->product_additional_image);
        }

        $this->product->save();

        session()->flash('success', __('messages_product_updated'));
        $this->redirectRoute('seller.products.index', navigate: true);
    }

    private function storeProductImage($imageFile, ?string $oldImage = null): string
    {
        if ($oldImage) {
            Storage::disk('public')->delete('products/'.$oldImage);
        }

        $image = Image::make($imageFile)
            ->resize(500, 500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80);

        $filename = uniqid().'.webp';
        Storage::disk('public')->put('products/'.$filename, (string) $image);

        return $filename;
    }

    public function render()
    {
        return view('frontend.seller.products.form', [
            'product' => $this->product,
            'productGalleryImages' => $this->product->imageGalleryUrls(),
            'countries' => $this->getEuropeanCountries(),
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
