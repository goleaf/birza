<?php

namespace App\Actions\Products\Comparison;

use App\Support\Products\ProductComparison;

class ClearProductCompareAction
{
    public function __construct(
        private readonly ProductComparison $comparison,
    ) {}

    public function handle(): void
    {
        $this->comparison->clear();
    }
}
