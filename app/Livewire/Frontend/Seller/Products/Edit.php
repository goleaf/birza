<?php

namespace App\Livewire\Frontend\Seller\Products;

use App\Actions\Notifications\SendProductModerationNotificationAction;
use App\Actions\Notifications\SendStockThresholdNotificationAction;
use App\Actions\Products\RecordProductAuditLogsAction;
use App\Livewire\Concerns\InteractsWithProductImageLibrary;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.frontend.app')]
class Edit extends Component
{
    use AuthorizesRequests;
    use InteractsWithProductImageLibrary;
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

    public function mount(Product $product): void
    {
        $this->authorize('update', $product);

        $this->product = $product->load('images');

        $this->category_id = (int) $product->category_id;
        $this->name = (string) ($product->name ?? '');
        $this->price = $product->price !== null ? (float) $product->price : null;
        $this->pack_type = (string) ($product->pack_type ?? '');
        $this->unit = (string) ($product->unit ?? Product::defaultUnit());
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

        $this->initializeProductImageLibrary($this->product);
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
        $this->authorize('update', $this->product);
        $this->authorize('manageGallery', $this->product);
        $this->ensureProductImageLibraryIsPresent();

        $validated = $this->validate();
        $previousStock = (int) $this->product->stock;
        $auditRecorder = app(RecordProductAuditLogsAction::class);
        $oldValues = $auditRecorder->snapshot($this->product);
        $oldImages = $auditRecorder->imagePaths($this->product);

        $this->product->forceFill([
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

        $this->product->save();
        $this->syncProductImageLibrary($this->product);
        $this->product->refresh()->load('images');

        $auditRecorder->updated(
            actor: Auth::guard('seller')->user(),
            product: $this->product,
            oldValues: $oldValues,
            oldImages: $oldImages,
            source: 'seller_product_edit',
        );

        app(SendStockThresholdNotificationAction::class)->handle($this->product, $previousStock);

        if (! $this->product->is_active) {
            app(SendProductModerationNotificationAction::class)->moderationRequired($this->product);
        }

        session()->flash('success', __('messages_product_updated'));
        $this->redirectRoute('seller.products.index', navigate: true);
    }

    public function render()
    {
        return view('frontend.seller.products.form', [
            'product' => $this->product,
            'countries' => $this->getEuropeanCountries(),
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
