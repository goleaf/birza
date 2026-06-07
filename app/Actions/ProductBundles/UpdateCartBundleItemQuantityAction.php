<?php

namespace App\Actions\ProductBundles;

use App\Models\Cart;
use App\Models\CartBundleItem;
use App\Models\ProductBundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCartBundleItemQuantityAction
{
    public function __construct(
        private readonly ValidateBundleAvailabilityAction $validateBundleAvailabilityAction,
        private readonly CalculateBundlePriceAction $calculateBundlePriceAction,
    ) {}

    public function handle(Cart $cart, ProductBundle $bundle, int $quantity): CartBundleItem
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => __('validation_min_numeric', ['min' => 1]),
            ]);
        }

        return DB::transaction(function () use ($cart, $bundle, $quantity): CartBundleItem {
            $bundle = ProductBundle::query()
                ->withActiveProducts()
                ->lockForUpdate()
                ->findOrFail($bundle->id);

            $item = $cart->bundleItems()
                ->where('product_bundle_id', $bundle->id)
                ->firstOrFail();

            $this->validateBundleAvailabilityAction->handle($bundle, $quantity);
            $price = $this->calculateBundlePriceAction->handle($bundle);

            $item->forceFill([
                'quantity' => $quantity,
                'unit_price' => $price['final_price'],
            ])->save();

            return $item->fresh(['productBundle.items.product.seller']);
        });
    }
}
