<?php

namespace App\Livewire\Frontend\Seller\Products;

use App\Actions\Notifications\SendProductModerationNotificationAction;
use App\Actions\Notifications\SendStockThresholdNotificationAction;
use App\Actions\Products\RecordProductAuditLogsAction;
use App\Livewire\Concerns\InteractsWithProductImageLibrary;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.frontend.app')]
class Create extends Component
{
    use AuthorizesRequests;
    use InteractsWithProductImageLibrary;
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

    public function mount(Category $categoryId): void
    {
        $this->authorize('create', Product::class);

        $this->selectedCategory = $categoryId->load('subcategories');

        $this->category_id = $this->selectedCategory->id;
        $this->unit = Product::defaultUnit();
        $this->initializeProductImageLibrary();

        foreach (config('app.locales') as $locale) {
            $this->description[$locale] = '';
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = array_merge([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'pack_type' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(Product::unitValues())],
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
        ], $this->productImageLibraryRules());

        $currentLocale = app()->getLocale();
        foreach (config('app.locales') as $locale) {
            $rules["description.$locale"] = $locale === $currentLocale
                ? ['required', 'string']
                : ['nullable', 'string'];
        }

        return $rules;
    }

    public function save(): void
    {
        $this->authorize('create', Product::class);
        $this->ensureProductImageLibraryIsPresent();

        $validated = $this->validate();

        $sellerId = Auth::guard('seller')->id();

        $product = new Product;
        $product->forceFill([
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
            'product_image' => '',
            'product_additional_image' => null,
        ]);

        $product->setTranslations('description', $validated['description']);

        $product->save();
        $this->authorize('manageGallery', $product);
        $this->syncProductImageLibrary($product);
        app(RecordProductAuditLogsAction::class)->created(
            actor: Auth::guard('seller')->user(),
            product: $product,
            source: 'seller_product_create',
        );
        app(SendProductModerationNotificationAction::class)->moderationRequired($product);
        app(SendStockThresholdNotificationAction::class)->handle($product);

        session()->flash('success', __('messages_product_created'));
        $this->redirectRoute('seller.products.index', navigate: true);
    }

    public function render()
    {
        $countries = $this->getEuropeanCountries();

        return view('frontend.seller.products.form', [
            'product' => null,
            'selectedCategory' => $this->selectedCategory,
            'countries' => $countries,
            'subcategories' => $this->selectedCategory->subcategories,
            'unitOptions' => Product::unitOptions(),
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
