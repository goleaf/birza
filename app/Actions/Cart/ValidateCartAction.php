<?php

namespace App\Actions\Cart;

use App\Actions\ProductBundles\ValidateBundleAvailabilityAction;
use App\Models\Cart;
use App\Models\CartBundleItem;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Users\Buyer;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ValidateCartAction
{
    public function __construct(
        private readonly ValidateBundleAvailabilityAction $validateBundleAvailabilityAction,
    ) {}

    /**
     * @return Collection<int, CartItem>
     */
    public function handle(Cart $cart, ?Buyer $buyer = null): Collection
    {
        if ($buyer !== null && (! $buyer->is_active || $buyer->trashed())) {
            throw ValidationException::withMessages([
                'buyer' => __('cart_messages_buyer_cannot_order'),
            ]);
        }

        $items = $cart->items()
            ->with(['product.seller'])
            ->get();
        $bundleItems = $cart->bundleItems()
            ->with(['productBundle.items.product.seller'])
            ->get();

        if ($items->isEmpty() && $bundleItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_empty_cart'),
            ]);
        }

        $items->each(fn (CartItem $item): Product => $this->validateItem($item));
        $bundleItems->each(fn (CartBundleItem $item): mixed => $this->validateBundleItem($item));

        return $items;
    }

    public function validateItem(CartItem $item): Product
    {
        $product = $item->product;

        if (! $product || $product->trashed()) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_product_not_found'),
            ]);
        }

        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_product_unavailable'),
            ]);
        }

        if (! $product->seller || ! $product->seller->is_active || $product->seller->trashed()) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_seller_unavailable'),
            ]);
        }

        if ((float) $product->price <= 0) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_price_invalid'),
            ]);
        }

        if ($item->quantity < max(1, (int) $product->min_order_count)) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_minimum_quantity', ['min' => $product->min_order_count]),
            ]);
        }

        if ($item->quantity > (int) $product->stock) {
            throw ValidationException::withMessages([
                'cart' => __('cart_messages_insufficient_stock'),
            ]);
        }

        return $product;
    }

    public function validateBundleItem(CartBundleItem $item): void
    {
        $bundle = $item->productBundle;

        if (! $bundle) {
            throw ValidationException::withMessages([
                'bundle' => __('bundles.messages.not_available'),
            ]);
        }

        $this->validateBundleAvailabilityAction->handle($bundle, (int) $item->quantity);
    }
}
