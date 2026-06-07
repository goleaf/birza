<?php

namespace App\Observers;

use App\Actions\StockAlerts\DetectBackInStockAction;
use App\Models\Product;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Product $product): void
    {
        app(DetectBackInStockAction::class)->handle(
            product: $product->fresh(['seller']) ?? $product,
            previousStock: (int) $product->getOriginal('stock'),
            wasActive: (bool) $product->getOriginal('is_active'),
        );
    }

    public function restored(Product $product): void
    {
        app(DetectBackInStockAction::class)->handle(
            product: $product->fresh(['seller']) ?? $product,
            previousStock: 0,
            wasActive: false,
        );
    }
}
