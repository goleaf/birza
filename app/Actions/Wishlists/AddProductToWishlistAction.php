<?php

namespace App\Actions\Wishlists;

use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddProductToWishlistAction
{
    public function __construct(
        private readonly CreateWishlistAction $createWishlist,
        private readonly RecordWishlistAuditLogAction $recordAuditLog,
    ) {}

    public function handle(Buyer $buyer, Product $product, ?Wishlist $wishlist = null): WishlistItem
    {
        return DB::transaction(function () use ($buyer, $product, $wishlist): WishlistItem {
            $wishlist = $wishlist ? $this->lockedWishlist($buyer, $wishlist) : $this->defaultWishlist($buyer);
            $product = $this->lockedProduct($product);

            $this->ensureProductCanBeSaved($product);

            if ($wishlist->items()->where('product_id', $product->id)->exists()) {
                throw ValidationException::withMessages([
                    'product' => __('wishlists.messages.already_exists'),
                ]);
            }

            $item = $wishlist->items()->create([
                'product_id' => $product->id,
            ]);

            $this->recordAuditLog->productAdded($buyer, $wishlist, $product);

            return $item->fresh(['wishlist', 'product']);
        });
    }

    private function lockedWishlist(Buyer $buyer, Wishlist $wishlist): Wishlist
    {
        abort_unless($wishlist->isOwnedBy($buyer), 403);

        return Wishlist::query()
            ->where('buyer_id', $buyer->id)
            ->lockForUpdate()
            ->findOrFail($wishlist->id);
    }

    private function defaultWishlist(Buyer $buyer): Wishlist
    {
        $wishlist = $buyer->wishlists()
            ->default()
            ->lockForUpdate()
            ->first();

        if ($wishlist) {
            return $wishlist;
        }

        return $this->createWishlist->handle($buyer, [
            'name' => __('wishlists.default_name'),
            'is_default' => true,
            'is_private' => true,
        ]);
    }

    private function lockedProduct(Product $product): Product
    {
        return Product::query()
            ->withTrashed()
            ->with('seller:id,is_active,deleted_at')
            ->lockForUpdate()
            ->findOrFail($product->id);
    }

    private function ensureProductCanBeSaved(Product $product): void
    {
        if (! $product->isVisibleToBuyers()) {
            throw ValidationException::withMessages([
                'product' => __('wishlists.messages.product_unavailable'),
            ]);
        }
    }
}
