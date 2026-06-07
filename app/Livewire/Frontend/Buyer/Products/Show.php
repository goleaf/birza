<?php

namespace App\Livewire\Frontend\Buyer\Products;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Messaging\StartConversationAction;
use App\Actions\ProductReports\CreateProductReportAction;
use App\Actions\Products\Comparison\AddProductToCompareAction;
use App\Actions\StockAlerts\CancelStockAlertAction;
use App\Actions\StockAlerts\CreateStockAlertAction;
use App\Actions\Wishlists\AddProductToWishlistAction;
use App\Enums\ProductReportReason;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Models\Wishlist;
use App\Support\Products\ProductComparison;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Show extends Component
{
    public Product $product;

    public int $quantity = 1;

    public ?int $wishlistId = null;

    public bool $reportModal = false;

    public ?string $reportReason = null;

    public ?string $reportMessage = null;

    public ?string $reporterEmail = null;

    public function mount(Product $product): void
    {
        if ($product->trashed() || $product->is_active === false) {
            abort(404);
        }

        $product->loadMissing([
            'seller:id,name,email,company_name',
            'country:id,country_name',
            'category:id,category_name,parent_category_id',
            'category.parent:id,category_name',
            'images',
            'category.attributes' => function ($query) {
                $query
                    ->select(['attributes.id', 'attributes.name'])
                    ->where('is_active', true);
            },
            'attributeValues:id,attribute_id,value',
        ]);

        $this->product = $product;
        $this->quantity = (int) ($product->min_order_count ?? 1);
        $this->wishlistId = $this->defaultWishlistId();

        if ($product->stock <= 0) {
            session()->flash('message', __('product_messages_out_of_stock'));
        }
    }

    public function addToCart(AddCartItemAction $action): void
    {
        $product = $this->product->fresh();

        if (! $product || $product->trashed() || $product->is_active === false) {
            session()->flash('message', __('cart_messages_product_not_found'));

            return;
        }

        if ((int) $product->stock === 0) {
            session()->flash('message', __('cart_messages_out_of_stock'));

            return;
        }

        $quantity = max(1, (int) $this->quantity);

        if ($quantity < (int) $product->min_order_count) {
            session()->flash('message', __('cart_messages_minimum_quantity', [
                'min' => $product->min_order_count,
                'product' => $product->name,
            ]));

            return;
        }

        if ($quantity > (int) $product->stock) {
            session()->flash('message', __('cart_messages_maximum_quantity', [
                'max' => $product->stock,
                'product' => $product->name,
            ]));

            return;
        }

        try {
            $action->handle(
                product: $product,
                quantity: $quantity,
                buyer: $this->buyer(),
                guestToken: $this->guestToken(),
            );
        } catch (ValidationException $exception) {
            session()->flash('message', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('cart_messages_product_added'));
    }

    public function addToCompare(AddProductToCompareAction $action): void
    {
        $product = $this->product->fresh();

        if (! $product) {
            session()->flash('message', __('compare.messages.product_unavailable'));

            return;
        }

        try {
            $action->handle($product);
        } catch (ValidationException $exception) {
            session()->flash('message', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('compare.messages.added'));
    }

    public function addToWishlist(AddProductToWishlistAction $action): void
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            session()->flash('message', __('wishlists.messages.login_required'));
            $this->redirectRoute('buyer.login', navigate: true);

            return;
        }

        $wishlist = $this->wishlistId
            ? Wishlist::query()
                ->where('buyer_id', $buyer->id)
                ->findOrFail($this->wishlistId)
            : null;

        try {
            $action->handle($buyer, $this->product, $wishlist);
        } catch (ValidationException $exception) {
            session()->flash('message', collect($exception->errors())->flatten()->first());

            return;
        }

        $this->wishlistId = $this->defaultWishlistId();

        session()->flash('success', __('wishlists.messages.product_added'));
    }

    public function contactSeller(StartConversationAction $action): void
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            session()->flash('message', __('messages.login_to_contact_seller'));
            $this->redirectRoute('buyer.login', navigate: true);

            return;
        }

        $product = $this->product->fresh(['seller']) ?? $this->product;

        try {
            $conversation = $action->forProduct($buyer, $product);
        } catch (AuthorizationException $exception) {
            session()->flash('message', $exception->getMessage() ?: __('messages.errors.not_allowed'));

            return;
        }

        $this->redirectRoute('buyer.messages.show', $conversation, navigate: true);
    }

    public function subscribeToStockAlert(CreateStockAlertAction $action): void
    {
        $buyer = $this->buyer();
        abort_if(! $buyer, 403);

        $product = $this->product->fresh(['seller']) ?? $this->product;

        try {
            $alert = $action->handle($product, $buyer);
        } catch (ValidationException $exception) {
            session()->flash('message', collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash(
            $alert->wasRecentlyCreated ? 'success' : 'message',
            $alert->wasRecentlyCreated
                ? __('stock_alerts.created_successfully')
                : __('stock_alerts.already_subscribed'),
        );
    }

    public function cancelStockAlert(int $alertId, CancelStockAlertAction $action): void
    {
        $buyer = $this->buyer();
        abort_if(! $buyer, 403);

        $alert = ProductStockAlert::query()
            ->where('product_id', $this->product->id)
            ->findOrFail($alertId);

        $action->handle($alert, $buyer);

        session()->flash('success', __('stock_alerts.cancelled_successfully'));
    }

    public function openReportModal(): void
    {
        $this->resetValidation();
        $this->reportReason = null;
        $this->reportMessage = null;
        $this->reporterEmail = null;
        $this->reportModal = true;
    }

    public function submitReport(CreateProductReportAction $action): void
    {
        $validated = $this->validate([
            'reportReason' => ['required', Rule::enum(ProductReportReason::class)],
            'reportMessage' => ['nullable', 'string', 'max:1000'],
            'reporterEmail' => [
                Rule::requiredIf($this->buyer() === null && (bool) config('marketplace.product_reports.allow_guest_reports', true)),
                'nullable',
                'email',
                'max:255',
            ],
        ], attributes: [
            'reportReason' => __('reports.product.reason'),
            'reportMessage' => __('reports.product.message'),
            'reporterEmail' => __('reports.product.reporter_email'),
        ]);

        $product = Product::withTrashed()->find($this->product->getKey());

        if (! $product) {
            $this->addError('product', __('reports.product.not_reportable'));

            return;
        }

        try {
            $action->handle(
                product: $product,
                reason: ProductReportReason::from($validated['reportReason']),
                message: $validated['reportMessage'] ?? null,
                buyer: $this->buyer(),
                seller: $this->seller(),
                reporterEmail: $validated['reporterEmail'] ?? null,
                request: request(),
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, (string) collect($messages)->first());
            }

            return;
        }

        $this->reportModal = false;
        $this->reportReason = null;
        $this->reportMessage = null;
        $this->reporterEmail = null;

        session()->flash('success', __('reports.product.created_successfully'));
    }

    public function render(): View
    {
        $comparison = app(ProductComparison::class);

        return view('frontend.buyer.products.show', [
            'product' => $this->product,
            'productSlides' => $this->getProductSlides($this->product),
            'productGalleryImages' => $this->product->imageGalleryUrls('thumb'),
            'attributeValuesByAttribute' => $this->product->attributeValues->groupBy('attribute_id'),
            'activeStockAlert' => $this->activeStockAlert(),
            'stockAlertBuyer' => $this->buyer() !== null,
            'message' => session('message'),
            'isCompared' => $comparison->has((int) $this->product->getKey()),
            'comparisonCount' => $comparison->count(),
            'comparisonLimit' => ProductComparison::MAX_PRODUCTS,
            'buyerWishlists' => $this->buyerWishlists(),
            'wishlistBuyer' => $this->buyer() !== null,
            'isWishlisted' => $this->isWishlisted(),
            'reportReasonOptions' => ProductReportReason::options(),
            'guestReportsEnabled' => (bool) config('marketplace.product_reports.allow_guest_reports', true),
            'isBuyerAuthenticated' => $this->buyer() !== null,
            'relatedBundles' => $this->relatedBundles(),
        ]);
    }

    protected function getProductSlides(Product $product): array
    {
        return collect($product->imageGalleryUrls('large'))
            ->map(fn (string $imageUrl, int $index): array => [
                'image' => $imageUrl,
                'alt' => trim($product->name.' '.($index + 1)),
                'lazy' => $index > 0,
            ])
            ->values()
            ->all();
    }

    private function buyer(): ?Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        return $buyer instanceof Buyer ? $buyer : null;
    }

    private function relatedBundles(): Collection
    {
        return ProductBundle::query()
            ->visible()
            ->withActiveProducts()
            ->whereHas('items', fn ($query) => $query->where('product_id', $this->product->id))
            ->latest('published_at')
            ->limit(3)
            ->get();
    }

    private function seller(): ?Seller
    {
        $seller = Auth::guard('seller')->user();

        return $seller instanceof Seller ? $seller : null;
    }

    private function activeStockAlert(): ?ProductStockAlert
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            return null;
        }

        return ProductStockAlert::query()
            ->select(['id', 'product_id', 'buyer_id', 'status', 'created_at'])
            ->active()
            ->where('product_id', $this->product->id)
            ->where('buyer_id', $buyer->id)
            ->first();
    }

    private function defaultWishlistId(): ?int
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            return null;
        }

        return Wishlist::query()
            ->forBuyer($buyer)
            ->where('is_default', true)
            ->value('id');
    }

    private function buyerWishlists(): Collection
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            return collect();
        }

        return Wishlist::query()
            ->forBuyer($buyer)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    private function isWishlisted(): bool
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            return false;
        }

        return $this->product
            ->wishlistItems()
            ->whereHas('wishlist', fn ($query) => $query->where('buyer_id', $buyer->id))
            ->exists();
    }

    private function guestToken(): ?string
    {
        if ($this->buyer() !== null) {
            return null;
        }

        if (! session()->has('cart_guest_token')) {
            session()->put('cart_guest_token', (string) Str::uuid());
        }

        return (string) session('cart_guest_token');
    }
}
