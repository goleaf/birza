<?php

namespace App\Actions\Wishlists;

use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\WishlistItem;
use Illuminate\Validation\ValidationException;
use LukePOLO\LaraCart\Facades\LaraCart;

class AddWishlistItemToCartAction
{
    public function handle(Buyer $buyer, WishlistItem $wishlistItem): void
    {
        $wishlistItem->loadMissing('wishlist', 'product.seller');

        if (! $wishlistItem->wishlist || ! $wishlistItem->wishlist->isOwnedBy($buyer)) {
            abort(403);
        }

        $product = $wishlistItem->product;

        if (! $product || ! $product->isPurchasableByBuyers()) {
            throw ValidationException::withMessages([
                'product' => __('wishlists.messages.product_unavailable'),
            ]);
        }

        $quantity = max(1, (int) $product->min_order_count);

        if ($this->addWithDatabaseCart($product, $quantity, $buyer)) {
            return;
        }

        LaraCart::add(
            $product->id,
            $product->name,
            $quantity,
            $product->price,
            [
                'image' => $product->product_image,
                'unit' => $product->unit,
                'seller_id' => $product->seller_id,
                'category_id' => $product->category_id,
                'min_order_price' => $product->min_order_price,
                'min_order_count' => $product->min_order_count,
                'is_organic' => $product->is_organic,
                'country_of_origin' => $product->country_of_origin,
                'package_weight' => $product->package_weight,
                'price_per_liter' => $product->price_per_liter,
                'stock' => $product->stock,
            ],
        );
    }

    private function addWithDatabaseCart(Product $product, int $quantity, Buyer $buyer): bool
    {
        if (! class_exists('App\\Actions\\Cart\\AddCartItemAction')) {
            return false;
        }

        $action = app('App\\Actions\\Cart\\AddCartItemAction');

        if (! method_exists($action, 'handle')) {
            return false;
        }

        $action->handle(
            product: $product,
            quantity: $quantity,
            buyer: $buyer,
        );

        return true;
    }
}
