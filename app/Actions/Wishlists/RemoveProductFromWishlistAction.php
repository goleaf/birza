<?php

namespace App\Actions\Wishlists;

use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Wishlist;

class RemoveProductFromWishlistAction
{
    public function __construct(
        private readonly RecordWishlistAuditLogAction $recordAuditLog,
    ) {}

    public function handle(Buyer $buyer, Wishlist $wishlist, Product $product): void
    {
        abort_unless($wishlist->isOwnedBy($buyer), 403);

        $deleted = $wishlist->items()
            ->where('product_id', $product->id)
            ->delete();

        if ($deleted > 0) {
            $this->recordAuditLog->productRemoved($buyer, $wishlist, $product);
        }
    }
}
