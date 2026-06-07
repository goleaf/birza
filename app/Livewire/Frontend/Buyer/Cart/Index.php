<?php

namespace App\Livewire\Frontend\Buyer\Cart;

use App\Actions\Cart\CalculateCartTotalsAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\CreateOrdersFromCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\ResolveCartAction;
use App\Actions\Cart\UpdateCartItemQuantityAction;
use App\Actions\ProductBundles\RemoveCartBundleItemAction;
use App\Actions\ProductBundles\UpdateCartBundleItemQuantityAction;
use App\Actions\Promotions\ApplyPromoCodeAction;
use App\Actions\Promotions\RemovePromoCodeAction;
use App\Models\Cart;
use App\Models\CartBundleItem;
use App\Models\CartItem;
use App\Models\ProductBundle;
use App\Models\Users\Buyer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Index extends Component
{
    public array $quantities = [];

    public array $bundleQuantities = [];

    public string $checkoutStep = 'review';

    public string $shippingAddress = '';

    public ?string $billingAddress = null;

    public string $paymentMethod = 'bank_transfer';

    public ?string $deliveryMethod = null;

    public string $promoCodeInput = '';

    public ?string $appliedPromoCode = null;

    public function mount(): void
    {
        $buyer = $this->buyer();
        $this->shippingAddress = (string) ($buyer?->address ?? '');
        $this->syncQuantitiesWithCart();
    }

    public function updateQuantity(int $cartItemId, UpdateCartItemQuantityAction $action): void
    {
        $cart = $this->cart();
        $item = $this->cartItem($cart, $cartItemId);
        $quantity = (int) ($this->quantities[$cartItemId] ?? $item->quantity);

        try {
            $action->handle($cart, $item->product, $quantity);
        } catch (ValidationException $exception) {
            $this->addError("quantities.$cartItemId", collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('cart_messages_quantity_updated'));
        $this->syncQuantitiesWithCart();
    }

    public function increaseQuantity(int $cartItemId, UpdateCartItemQuantityAction $action): void
    {
        $cart = $this->cart();
        $item = $this->cartItem($cart, $cartItemId);
        $this->quantities[$cartItemId] = $item->quantity + 1;
        $this->updateQuantity($cartItemId, $action);
    }

    public function decreaseQuantity(int $cartItemId, UpdateCartItemQuantityAction $action, RemoveCartItemAction $removeAction): void
    {
        $cart = $this->cart();
        $item = $this->cartItem($cart, $cartItemId);

        if ($item->quantity <= 1) {
            $removeAction->handle($cart, $item->product);
            $this->syncQuantitiesWithCart();

            return;
        }

        $this->quantities[$cartItemId] = $item->quantity - 1;
        $this->updateQuantity($cartItemId, $action);
    }

    public function removeItem(int $cartItemId, RemoveCartItemAction $action): void
    {
        $cart = $this->cart();
        $item = $this->cartItem($cart, $cartItemId);

        $action->handle($cart, $item->product);
        unset($this->quantities[$cartItemId]);

        session()->flash('success', __('cart_messages_product_removed'));
    }

    public function updateBundleQuantity(int $cartBundleItemId, UpdateCartBundleItemQuantityAction $action): void
    {
        $cart = $this->cart();
        $item = $this->cartBundleItem($cart, $cartBundleItemId);
        $quantity = (int) ($this->bundleQuantities[$cartBundleItemId] ?? $item->quantity);
        $bundle = $item->productBundle;

        if (! $bundle instanceof ProductBundle) {
            $this->addError("bundleQuantities.$cartBundleItemId", __('bundles.messages.not_available'));

            return;
        }

        try {
            $action->handle($cart, $bundle, $quantity);
        } catch (ValidationException $exception) {
            $this->addError("bundleQuantities.$cartBundleItemId", collect($exception->errors())->flatten()->first());

            return;
        }

        session()->flash('success', __('cart_messages_quantity_updated'));
        $this->syncQuantitiesWithCart();
    }

    public function increaseBundleQuantity(int $cartBundleItemId, UpdateCartBundleItemQuantityAction $action): void
    {
        $cart = $this->cart();
        $item = $this->cartBundleItem($cart, $cartBundleItemId);
        $this->bundleQuantities[$cartBundleItemId] = $item->quantity + 1;
        $this->updateBundleQuantity($cartBundleItemId, $action);
    }

    public function decreaseBundleQuantity(int $cartBundleItemId, UpdateCartBundleItemQuantityAction $action, RemoveCartBundleItemAction $removeAction): void
    {
        $cart = $this->cart();
        $item = $this->cartBundleItem($cart, $cartBundleItemId);
        $bundle = $item->productBundle;

        if (! $bundle instanceof ProductBundle) {
            session()->flash('error', __('bundles.messages.not_available'));

            return;
        }

        if ($item->quantity <= 1) {
            $removeAction->handle($cart, $bundle);
            unset($this->bundleQuantities[$cartBundleItemId]);
            $this->syncQuantitiesWithCart();

            return;
        }

        $this->bundleQuantities[$cartBundleItemId] = $item->quantity - 1;
        $this->updateBundleQuantity($cartBundleItemId, $action);
    }

    public function removeBundleItem(int $cartBundleItemId, RemoveCartBundleItemAction $action): void
    {
        $cart = $this->cart();
        $item = $this->cartBundleItem($cart, $cartBundleItemId);
        $bundle = $item->productBundle;

        if (! $bundle instanceof ProductBundle) {
            session()->flash('error', __('bundles.messages.not_available'));

            return;
        }

        $action->handle($cart, $bundle);
        unset($this->bundleQuantities[$cartBundleItemId]);

        session()->flash('success', __('bundles.messages.removed_from_cart'));
    }

    public function clearCart(ClearCartAction $action): void
    {
        $action->handle($this->cart());
        $this->quantities = [];
        $this->bundleQuantities = [];
        $this->checkoutStep = 'review';
        $this->promoCodeInput = '';
        $this->appliedPromoCode = null;

        session()->flash('success', __('cart_messages_cart_cleared'));
    }

    public function applyPromoCode(ApplyPromoCodeAction $action): void
    {
        $this->resetErrorBag('promo_code');

        $code = trim($this->promoCodeInput);

        if ($code === '') {
            $this->addError('promo_code', __('promo_codes.invalid'));

            return;
        }

        try {
            $totals = $action->handle($this->cart(), $this->buyer(), $code);
        } catch (ValidationException $exception) {
            $this->appliedPromoCode = null;
            $this->addError('promo_code', (string) collect($exception->errors())->flatten()->first());

            return;
        }

        $this->appliedPromoCode = (string) $totals['promo_code'];
        $this->promoCodeInput = $this->appliedPromoCode;

        session()->flash('success', __('promo_codes.applied_successfully'));
    }

    public function removePromoCode(RemovePromoCodeAction $action): void
    {
        $this->appliedPromoCode = $action->handle();
        $this->promoCodeInput = '';
        $this->resetErrorBag('promo_code');

        session()->flash('success', __('promo_codes.removed_successfully'));
    }

    public function beginCheckout(): void
    {
        if (! $this->buyer()) {
            session()->flash('error', __('cart_messages_login_required'));
            $this->redirectRoute('buyer.login', navigate: true);

            return;
        }

        $cart = $this->cart();

        if ($cart->items()->count() === 0 && $cart->bundleItems()->count() === 0) {
            session()->flash('error', __('cart_messages_empty_cart'));

            return;
        }

        $this->checkoutStep = 'confirmation';
    }

    public function checkout(CreateOrdersFromCartAction $action): void
    {
        $buyer = $this->buyer();

        if (! $buyer) {
            session()->flash('error', __('cart_messages_login_required'));
            $this->redirectRoute('buyer.login', navigate: true);

            return;
        }

        try {
            $action->handle($this->cart(), $buyer, [
                'shipping_address' => $this->shippingAddress,
                'billing_address' => $this->billingAddress,
                'delivery_method' => $this->deliveryMethod,
                'payment_method' => $this->paymentMethod,
                'promo_code' => $this->appliedPromoCode,
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, (string) collect($messages)->first());
            }

            return;
        }

        session()->flash('success', __('cart_messages_order_placed'));
        $this->redirectRoute('buyer.orders.index', navigate: true);
    }

    public function render(CalculateCartTotalsAction $totalsAction): View
    {
        $cart = $this->cart();
        $cartItems = $cart->items()
            ->with(['product.primaryImage', 'product.seller'])
            ->get();
        $cartBundleItems = $cart->bundleItems()
            ->with(['productBundle.seller', 'productBundle.items.product.primaryImage', 'productBundle.items.product.seller'])
            ->get();

        $this->syncQuantitiesWithItems($cartItems);
        $this->syncBundleQuantitiesWithItems($cartBundleItems);

        $promoCodeError = null;

        try {
            $cartTotals = $totalsAction->handle($cart, $this->buyer(), $this->appliedPromoCode);
        } catch (ValidationException $exception) {
            $promoCodeError = (string) collect($exception->errors())->flatten()->first();
            $cartTotals = $totalsAction->handle($cart, $this->buyer());
        }

        return view('frontend.buyer.cart.index', [
            'cart' => $cart,
            'cartItemRows' => $this->cartItemRows($cartItems),
            'cartBundleItemRows' => $this->cartBundleItemRows($cartBundleItems),
            'cartTotals' => $cartTotals,
            'hasCartItems' => $cartItems->isNotEmpty() || $cartBundleItems->isNotEmpty(),
            'isGuestCart' => ! $this->buyer(),
            'promoCodeError' => $promoCodeError,
        ]);
    }

    private function cart(): Cart
    {
        return app(ResolveCartAction::class)->handle($this->buyer(), $this->guestToken());
    }

    private function buyer(): ?Buyer
    {
        $buyer = Auth::guard('buyer')->user();

        return $buyer instanceof Buyer ? $buyer : null;
    }

    private function guestToken(): string
    {
        if (! session()->has('cart_guest_token')) {
            session()->put('cart_guest_token', (string) Str::uuid());
        }

        return (string) session('cart_guest_token');
    }

    private function cartItem(Cart $cart, int $cartItemId): CartItem
    {
        return $cart->items()
            ->with('product.seller')
            ->findOrFail($cartItemId);
    }

    private function cartBundleItem(Cart $cart, int $cartBundleItemId): CartBundleItem
    {
        return $cart->bundleItems()
            ->with('productBundle.items.product.seller')
            ->findOrFail($cartBundleItemId);
    }

    private function syncQuantitiesWithCart(): void
    {
        $this->syncQuantitiesWithItems(
            $this->cart()->items()->get()
        );
        $this->syncBundleQuantitiesWithItems(
            $this->cart()->bundleItems()->get()
        );
    }

    private function syncQuantitiesWithItems($cartItems): void
    {
        foreach ($cartItems as $item) {
            $this->quantities[$item->id] = (int) ($this->quantities[$item->id] ?? $item->quantity);
        }
    }

    private function syncBundleQuantitiesWithItems($cartBundleItems): void
    {
        foreach ($cartBundleItems as $item) {
            $this->bundleQuantities[$item->id] = (int) ($this->bundleQuantities[$item->id] ?? $item->quantity);
        }
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @return Collection<int, array{
     *     item: CartItem,
     *     product: mixed,
     *     image_url: string,
     *     product_url: ?string,
     *     title: string,
     *     seller_name: string,
     *     current_price: float,
     *     stored_price: float,
     *     subtotal: float,
     *     has_price_changed: bool,
     *     is_unavailable: bool
     * }>
     */
    private function cartItemRows(Collection $cartItems): Collection
    {
        return $cartItems->map(function (CartItem $item): array {
            $product = $item->product;
            $currentPrice = (float) ($product?->price ?? $item->unit_price);
            $storedPrice = (float) $item->unit_price;
            $isUnavailable = ! $product
                || $product->trashed()
                || ! $product->is_active
                || $item->quantity > (int) $product->stock
                || ! $product->seller?->is_active;

            return [
                'item' => $item,
                'product' => $product,
                'image_url' => $product?->imageUrl('thumb') ?? asset((string) config('images.fallbacks.product')),
                'product_url' => $product && ! $product->trashed() ? route('buyer.products.show', $product) : null,
                'title' => $product?->name ?? __('common_unnamed_product'),
                'seller_name' => $product?->seller?->company_name ?? __('common_not_specified'),
                'current_price' => $currentPrice,
                'stored_price' => $storedPrice,
                'subtotal' => round($currentPrice * (int) $item->quantity, 2),
                'has_price_changed' => $product !== null && abs($currentPrice - $storedPrice) > 0.0001,
                'is_unavailable' => $isUnavailable,
            ];
        });
    }

    /**
     * @param  Collection<int, CartBundleItem>  $cartBundleItems
     * @return Collection<int, array<string, mixed>>
     */
    private function cartBundleItemRows(Collection $cartBundleItems): Collection
    {
        return $cartBundleItems->map(function (CartBundleItem $item): array {
            $bundle = $item->productBundle;
            $currentPrice = $bundle instanceof ProductBundle ? $bundle->finalPrice() : (float) $item->unit_price;
            $storedPrice = (float) $item->unit_price;
            $isUnavailable = ! ($bundle instanceof ProductBundle)
                || $bundle->trashed()
                || ! $bundle->isVisibleToBuyers()
                || $bundle->items->contains(fn ($bundleItem): bool => ! $bundleItem->product
                    || $bundleItem->product->trashed()
                    || ! $bundleItem->product->is_active
                    || (int) $bundleItem->product->stock < ((int) $bundleItem->quantity * (int) $item->quantity));

            return [
                'item' => $item,
                'bundle' => $bundle,
                'image_url' => $bundle instanceof ProductBundle ? $bundle->imageUrl() : asset((string) config('images.fallbacks.product')),
                'bundle_url' => $bundle instanceof ProductBundle && ! $bundle->trashed() ? route('buyer.bundles.show', $bundle) : null,
                'title' => $bundle?->name ?? __('bundles.unnamed'),
                'seller_name' => $bundle?->seller?->company_name ?? __('common_not_specified'),
                'base_price' => $bundle instanceof ProductBundle ? $bundle->basePrice() : 0.0,
                'discount_amount' => $bundle instanceof ProductBundle ? $bundle->discountAmount() : 0.0,
                'current_price' => $currentPrice,
                'stored_price' => $storedPrice,
                'subtotal' => round($currentPrice * (int) $item->quantity, 2),
                'has_price_changed' => $bundle instanceof ProductBundle && abs($currentPrice - $storedPrice) > 0.0001,
                'is_unavailable' => $isUnavailable,
                'included_products' => $bundle instanceof ProductBundle
                    ? $bundle->items->map(fn ($bundleItem): array => [
                        'title' => $bundleItem->product?->name ?? __('common_unnamed_product'),
                        'quantity' => (int) $bundleItem->quantity,
                    ])->values()
                    : collect(),
            ];
        });
    }
}
