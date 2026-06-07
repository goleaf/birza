<?php

namespace App\Actions\Products\Comparison;

use App\Models\Product;
use App\Support\Products\ProductComparison;
use Illuminate\Validation\ValidationException;

class AddProductToCompareAction
{
    public function __construct(
        private readonly ProductComparison $comparison,
    ) {}

    public function handle(Product $product): void
    {
        if (! $this->comparison->isProductComparable($product)) {
            throw ValidationException::withMessages([
                'compare' => __('compare.messages.product_unavailable'),
            ]);
        }

        $productId = (int) $product->getKey();

        if ($this->comparison->has($productId)) {
            throw ValidationException::withMessages([
                'compare' => __('compare.messages.already_exists'),
            ]);
        }

        if ($this->comparison->count() >= ProductComparison::MAX_PRODUCTS) {
            throw ValidationException::withMessages([
                'compare' => __('compare.messages.limit_reached', [
                    'limit' => ProductComparison::MAX_PRODUCTS,
                ]),
            ]);
        }

        $this->comparison->add($productId);
    }
}
