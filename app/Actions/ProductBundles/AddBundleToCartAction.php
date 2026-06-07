<?php

namespace App\Actions\ProductBundles;

use App\Actions\Cart\ResolveCartAction;
use App\Models\Cart;
use App\Models\ProductBundle;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddBundleToCartAction
{
    public function __construct(
        private readonly ResolveCartAction $resolveCartAction,
        private readonly ValidateBundleAvailabilityAction $validateBundleAvailabilityAction,
        private readonly CalculateBundlePriceAction $calculateBundlePriceAction,
    ) {}

    public function handle(
        ProductBundle $bundle,
        int $quantity,
        ?Buyer $buyer = null,
        ?string $guestToken = null,
    ): Cart {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => __('validation_min_numeric', ['min' => 1]),
            ]);
        }

        return DB::transaction(function () use ($bundle, $quantity, $buyer, $guestToken): Cart {
            $cart = $this->resolveCartAction->handle($buyer, $guestToken);
            $bundle = ProductBundle::query()
                ->withActiveProducts()
                ->lockForUpdate()
                ->findOrFail($bundle->id);

            $item = $cart->bundleItems()->firstOrNew([
                'product_bundle_id' => $bundle->id,
            ]);

            $newQuantity = (int) $item->quantity + $quantity;
            $this->validateBundleAvailabilityAction->handle($bundle, $newQuantity);
            $price = $this->calculateBundlePriceAction->handle($bundle);

            $item->quantity = $newQuantity;
            $item->unit_price = $price['final_price'];
            $item->save();

            return $cart->fresh([
                'items.product.seller',
                'bundleItems.productBundle.items.product.seller',
            ]);
        });
    }
}
