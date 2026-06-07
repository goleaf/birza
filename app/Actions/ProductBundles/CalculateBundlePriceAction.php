<?php

namespace App\Actions\ProductBundles;

use App\Models\ProductBundle;
use App\Models\ProductBundleItem;

class CalculateBundlePriceAction
{
    /**
     * @return array{
     *     base_price: float,
     *     discount_amount: float,
     *     final_price: float,
     *     products: array<int, array{product_id: int, title: string, unit_price: float, quantity: int, line_total: float}>
     * }
     */
    public function handle(ProductBundle $bundle, int $bundleQuantity = 1): array
    {
        $bundle = $bundle->loadMissing('items.product');
        $bundleQuantity = max(1, $bundleQuantity);

        $products = $bundle->items
            ->map(function (ProductBundleItem $item) use ($bundleQuantity): array {
                $quantity = (int) $item->quantity * $bundleQuantity;
                $unitPrice = (float) $item->product->price;

                return [
                    'product_id' => (int) $item->product_id,
                    'title' => (string) $item->product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_total' => round($unitPrice * $quantity, 2),
                ];
            })
            ->values();

        $singleBasePrice = round((float) $bundle->items->sum(
            fn (ProductBundleItem $item): float => (float) $item->product->price * (int) $item->quantity,
        ), 2);
        $singleDiscountAmount = $bundle->discountAmount($singleBasePrice);
        $basePrice = round($singleBasePrice * $bundleQuantity, 2);
        $discountAmount = round($singleDiscountAmount * $bundleQuantity, 2);

        return [
            'base_price' => $basePrice,
            'discount_amount' => $discountAmount,
            'final_price' => round(max(0, $basePrice - $discountAmount), 2),
            'products' => $products->all(),
        ];
    }
}
