<?php

namespace App\Actions\ProductBundles;

use App\Models\Cart;
use App\Models\ProductBundle;

class RemoveCartBundleItemAction
{
    public function handle(Cart $cart, ProductBundle $bundle): void
    {
        $cart->bundleItems()
            ->where('product_bundle_id', $bundle->id)
            ->delete();
    }
}
