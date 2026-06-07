<?php

namespace App\Actions\Wishlists;

use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

class ClearWishlistAction
{
    public function __construct(
        private readonly RecordWishlistAuditLogAction $recordAuditLog,
    ) {}

    public function handle(Buyer $buyer, Wishlist $wishlist): void
    {
        abort_unless($wishlist->isOwnedBy($buyer), 403);

        DB::transaction(function () use ($buyer, $wishlist): void {
            $lockedWishlist = Wishlist::query()
                ->where('buyer_id', $buyer->id)
                ->withCount('items')
                ->lockForUpdate()
                ->findOrFail($wishlist->id);

            $itemsCount = (int) $lockedWishlist->items_count;
            $lockedWishlist->items()->delete();

            if ($itemsCount > 0) {
                $this->recordAuditLog->cleared($buyer, $lockedWishlist, $itemsCount);
            }
        });
    }
}
