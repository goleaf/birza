<?php

namespace App\Actions\Cart;

use App\Actions\ProductBundles\AddBundleToCartAction;
use App\Models\Cart;
use App\Models\CartBundleItem;
use App\Models\CartItem;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\DB;

class MergeGuestCartAction
{
    public function __construct(
        private readonly AddCartItemAction $addCartItemAction,
        private readonly AddBundleToCartAction $addBundleToCartAction,
        private readonly ResolveCartAction $resolveCartAction,
    ) {}

    public function handle(string $guestToken, Buyer $buyer): Cart
    {
        return DB::transaction(function () use ($guestToken, $buyer): Cart {
            $buyerCart = $this->resolveCartAction->handle($buyer);
            $guestCart = Cart::query()
                ->active()
                ->with(['items.product.seller', 'bundleItems.productBundle.items.product.seller'])
                ->where('guest_token', $guestToken)
                ->lockForUpdate()
                ->first();

            if (! $guestCart) {
                return $buyerCart->fresh(['items.product.seller']);
            }

            $guestCart->items->each(function (CartItem $item) use ($buyer): void {
                if ($item->product) {
                    $this->addCartItemAction->handle($item->product, $item->quantity, $buyer);
                }
            });

            $guestCart->bundleItems->each(function (CartBundleItem $item) use ($buyer): void {
                if ($item->productBundle) {
                    $this->addBundleToCartAction->handle($item->productBundle, $item->quantity, $buyer);
                }
            });

            $guestCart->items()->delete();
            $guestCart->bundleItems()->delete();
            $guestCart->forceFill(['status' => Cart::STATUS_CONVERTED])->save();

            return $buyerCart->fresh(['items.product.seller']);
        });
    }
}
