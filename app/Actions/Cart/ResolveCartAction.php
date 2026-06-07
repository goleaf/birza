<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\Users\Buyer;
use Illuminate\Validation\ValidationException;

class ResolveCartAction
{
    public function handle(?Buyer $buyer = null, ?string $guestToken = null): Cart
    {
        if ($buyer !== null) {
            return Cart::query()
                ->with(['items.product.seller', 'bundleItems.productBundle.items.product.seller'])
                ->firstOrCreate([
                    'user_id' => $buyer->id,
                    'status' => Cart::STATUS_ACTIVE,
                ], [
                    'guest_token' => null,
                ]);
        }

        if (filled($guestToken)) {
            return Cart::query()
                ->with(['items.product.seller', 'bundleItems.productBundle.items.product.seller'])
                ->firstOrCreate([
                    'guest_token' => $guestToken,
                    'status' => Cart::STATUS_ACTIVE,
                ], [
                    'user_id' => null,
                ]);
        }

        throw ValidationException::withMessages([
            'cart' => __('cart_messages_unresolvable_cart'),
        ]);
    }
}
