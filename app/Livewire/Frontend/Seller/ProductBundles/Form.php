<?php

namespace App\Livewire\Frontend\Seller\ProductBundles;

use App\Actions\ProductBundles\SaveProductBundleAction;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\Users\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.frontend.app')]
class Form extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?ProductBundle $productBundle = null;

    public string $name = '';

    public string $slug = '';

    public ?string $description = null;

    public string $status = ProductBundle::STATUS_DRAFT;

    public ?string $discount_type = null;

    public ?float $discount_value = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public $image = null;

    public ?string $currentImagePath = null;

    /** @var array<int, int|string> */
    public array $selectedProductIds = [];

    /** @var array<int|string, int|string> */
    public array $itemQuantities = [];

    /** @var array<int|string, int|string> */
    public array $itemSortOrders = [];

    public function mount(?ProductBundle $productBundle = null): void
    {
        if ($productBundle instanceof ProductBundle && $productBundle->exists) {
            $this->authorize('update', $productBundle);
            $this->productBundle = $productBundle->load('items.product');
            $this->fillFromBundle($this->productBundle);

            return;
        }

        $this->authorize('create', ProductBundle::class);
    }

    public function updatedName(string $value): void
    {
        if ($this->slug === '') {
            $this->slug = Str::slug($value);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('product_bundles', 'slug')->ignore($this->productBundle?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(ProductBundle::statuses())],
            'discount_type' => ['nullable', Rule::in(ProductBundle::discountTypes())],
            'discount_value' => ['nullable', 'numeric', 'min:0.01'],
            'starts_at' => ['nullable', 'date', 'before:ends_at'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'image' => ['nullable', 'image', 'max:2048'],
            'selectedProductIds' => ['array'],
            'selectedProductIds.*' => ['integer', Rule::exists('products', 'id')],
            'itemQuantities.*' => ['integer', 'min:1'],
            'itemSortOrders.*' => ['integer', 'min:0'],
        ];
    }

    public function save(SaveProductBundleAction $action): void
    {
        $seller = $this->seller();
        $this->authorize($this->productBundle instanceof ProductBundle ? 'update' : 'create', $this->productBundle ?? ProductBundle::class);

        $validated = $this->validate();
        $storedImagePath = null;

        if ($this->image !== null) {
            $storedImagePath = $this->image->store('product-bundles', 'public');
        }

        try {
            $bundle = $action->handle(
                seller: $seller,
                data: [
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'],
                    'discount_type' => $validated['discount_type'] ?? null,
                    'discount_value' => $validated['discount_value'] ?? null,
                    'starts_at' => $validated['starts_at'] ?? null,
                    'ends_at' => $validated['ends_at'] ?? null,
                    'image_path' => $storedImagePath ?? $this->currentImagePath,
                    'product_ids' => $this->selectedProductIds,
                    'quantities' => $this->itemQuantities,
                    'sort_orders' => $this->itemSortOrders,
                ],
                bundle: $this->productBundle,
                actor: $seller,
            );
        } catch (ValidationException $exception) {
            if ($storedImagePath !== null) {
                Storage::disk('public')->delete($storedImagePath);
            }

            throw $exception;
        }

        if ($storedImagePath !== null && filled($this->currentImagePath) && $this->currentImagePath !== $storedImagePath) {
            Storage::disk('public')->delete($this->currentImagePath);
        }

        session()->flash('success', $this->productBundle instanceof ProductBundle
            ? __('bundles.messages.updated')
            : __('bundles.messages.created'));
        $this->redirectRoute('seller.bundles.edit', $bundle, navigate: true);
    }

    public function render(): View
    {
        $products = Product::query()
            ->select(['id', 'seller_id', 'name', 'price', 'stock', 'is_active'])
            ->where('seller_id', $this->seller()->id)
            ->orderBy('name')
            ->get();

        return view('livewire.frontend.seller.product-bundles.form', [
            'products' => $products,
            'statusOptions' => $this->statusOptions(),
            'discountTypeOptions' => $this->discountTypeOptions(),
            'bundlePreviewBasePrice' => $this->bundlePreviewBasePrice($products),
            'bundlePreviewDiscountAmount' => $this->bundlePreviewDiscountAmount($products),
            'bundlePreviewFinalPrice' => max(0, $this->bundlePreviewBasePrice($products) - $this->bundlePreviewDiscountAmount($products)),
        ]);
    }

    private function fillFromBundle(ProductBundle $bundle): void
    {
        $this->name = (string) $bundle->name;
        $this->slug = (string) $bundle->slug;
        $this->description = $bundle->description;
        $this->status = (string) $bundle->status;
        $this->discount_type = $bundle->discount_type;
        $this->discount_value = $bundle->discount_value !== null ? (float) $bundle->discount_value : null;
        $this->starts_at = $bundle->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $bundle->ends_at?->format('Y-m-d\TH:i');
        $this->currentImagePath = $bundle->image_path;
        $this->selectedProductIds = $bundle->items->pluck('product_id')->map(fn (mixed $productId): int => (int) $productId)->values()->all();

        $bundle->items->each(function ($item): void {
            $this->itemQuantities[(int) $item->product_id] = (int) $item->quantity;
            $this->itemSortOrders[(int) $item->product_id] = (int) $item->sort_order;
        });
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return collect(ProductBundle::statuses())
            ->map(fn (string $status): array => [
                'value' => $status,
                'label' => __('bundles.status.'.$status),
            ])
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function discountTypeOptions(): array
    {
        return collect(ProductBundle::discountTypes())
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => __('bundles.discount_types.'.$type),
            ])
            ->all();
    }

    private function bundlePreviewBasePrice($products): float
    {
        return round((float) $products
            ->whereIn('id', collect($this->selectedProductIds)->map(fn (mixed $productId): int => (int) $productId))
            ->sum(fn (Product $product): float => (float) $product->price * max(1, (int) ($this->itemQuantities[$product->id] ?? 1))), 2);
    }

    private function bundlePreviewDiscountAmount($products): float
    {
        $basePrice = $this->bundlePreviewBasePrice($products);

        if ($this->discount_type === ProductBundle::DISCOUNT_TYPE_PERCENTAGE && $this->discount_value !== null) {
            return round(min($basePrice, $basePrice * ((float) $this->discount_value / 100)), 2);
        }

        if ($this->discount_type === ProductBundle::DISCOUNT_TYPE_FIXED_AMOUNT && $this->discount_value !== null) {
            return round(min($basePrice, (float) $this->discount_value), 2);
        }

        return 0.0;
    }

    private function seller(): Seller
    {
        $seller = Auth::guard('seller')->user();

        abort_unless($seller instanceof Seller, 403);

        return $seller;
    }
}
