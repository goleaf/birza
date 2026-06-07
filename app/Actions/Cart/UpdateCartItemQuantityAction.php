<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCartItemQuantityAction
{
    public function __construct(
        private readonly ValidateCartAction $validateCartAction,
    ) {}

    public function handle(Cart $cart, Product $product, int $quantity): CartItem
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => __('validation_min_numeric', ['min' => 1]),
            ]);
        }

        return DB::transaction(function () use ($cart, $product, $quantity): CartItem {
            $item = $cart->items()
                ->where('product_id', $product->id)
                ->firstOrFail();

            $item->forceFill([
                'quantity' => $quantity,
                'unit_price' => $product->price,
            ])->save();

            $item->loadMissing('product.seller');
            $this->validateCartAction->validateItem($item);

            return $item->fresh(['product.seller']);
        });
    }
}
