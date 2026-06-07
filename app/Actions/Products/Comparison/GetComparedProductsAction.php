<?php

namespace App\Actions\Products\Comparison;

use App\Models\Product;
use App\Support\Products\ProductComparison;
use Illuminate\Support\Collection;

class GetComparedProductsAction
{
    public function __construct(
        private readonly ProductComparison $comparison,
    ) {}

    /**
     * @return Collection<int, Product>
     */
    public function handle(): Collection
    {
        return $this->comparison->products();
    }
}
