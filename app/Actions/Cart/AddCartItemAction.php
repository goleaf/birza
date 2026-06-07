<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\DB;

class AddCartItemAction
{
    public function __construct(
        private readonly ResolveCartAction $resolveCartAction,
        private readonly ValidateCartAction $validateCartAction,
    ) {}

    public function handle(
        Product $product,
        int $quantity,
        ?Buyer $buyer = null,
        ?string $guestToken = null,
    ): Cart {
        return DB::transaction(function () use ($product, $quantity, $buyer, $guestToken): Cart {
            $cart = $this->resolveCartAction->handle($buyer, $guestToken);

            $item = $cart->items()->firstOrNew([
                'product_id' => $product->id,
            ]);

            $item->quantity = (int) $item->quantity + $quantity;
            $item->unit_price = $product->price;
            $item->save();

            $this->validateCartItem($item);

            return $cart->fresh(['items.product.seller']);
        });
    }

    private function validateCartItem(CartItem $item): void
    {
        $item->loadMissing('product.seller');
        $this->validateCartAction->validateItem($item);
    }
}
