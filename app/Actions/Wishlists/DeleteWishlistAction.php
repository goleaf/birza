<?php

namespace App\Actions\Wishlists;

use App\Models\Users\Buyer;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

class DeleteWishlistAction
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

            $oldValues = $this->recordAuditLog->snapshot($lockedWishlist);
            $wasDefault = (bool) $lockedWishlist->is_default;

            $this->recordAuditLog->deleted($buyer, $lockedWishlist, array_merge($oldValues, [
                'items_count' => $lockedWishlist->items_count,
            ]));

            $lockedWishlist->delete();

            if ($wasDefault) {
                $replacement = $buyer->wishlists()
                    ->latest()
                    ->first();

                if ($replacement) {
                    $replacement->forceFill(['is_default' => true])->save();
                }
            }
        });
    }
}
