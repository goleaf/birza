<?php

namespace App\Actions\Wishlists;

use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MoveProductBetweenWishlistsAction
{
    public function __construct(
        private readonly RecordWishlistAuditLogAction $recordAuditLog,
    ) {}

    public function handle(Buyer $buyer, Wishlist $fromWishlist, Wishlist $toWishlist, Product $product): void
    {
        abort_unless($fromWishlist->isOwnedBy($buyer) && $toWishlist->isOwnedBy($buyer), 403);

        if ((int) $fromWishlist->id === (int) $toWishlist->id) {
            return;
        }

        DB::transaction(function () use ($buyer, $fromWishlist, $toWishlist, $product): void {
            $from = Wishlist::query()
                ->where('buyer_id', $buyer->id)
                ->lockForUpdate()
                ->findOrFail($fromWishlist->id);

            $to = Wishlist::query()
                ->where('buyer_id', $buyer->id)
                ->lockForUpdate()
                ->findOrFail($toWishlist->id);

            $item = $from->items()
                ->where('product_id', $product->id)
                ->firstOrFail();

            if ($to->items()->where('product_id', $product->id)->exists()) {
                throw ValidationException::withMessages([
                    'product' => __('wishlists.messages.already_exists'),
                ]);
            }

            $to->items()->create([
                'product_id' => $product->id,
            ]);

            $item->delete();

            $this->recordAuditLog->productMoved($buyer, $from, $to, $product);
        });
    }
}
