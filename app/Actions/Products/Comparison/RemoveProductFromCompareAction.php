<?php

namespace App\Actions\Products\Comparison;

use App\Models\Product;
use App\Support\Products\ProductComparison;

class RemoveProductFromCompareAction
{
    public function __construct(
        private readonly ProductComparison $comparison,
    ) {}

    public function handle(Product|int $product): void
    {
        $productId = $product instanceof Product
            ? (int) $product->getKey()
            : $product;

        $this->comparison->remove($productId);
    }
}
