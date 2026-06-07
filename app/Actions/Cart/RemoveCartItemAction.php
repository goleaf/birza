<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\Product;

class RemoveCartItemAction
{
    public function handle(Cart $cart, Product $product): void
    {
        $cart->items()
            ->where('product_id', $product->id)
            ->delete();
    }
}
