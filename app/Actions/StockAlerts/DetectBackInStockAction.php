<?php

namespace App\Actions\StockAlerts;

use App\Models\Product;

class DetectBackInStockAction
{
    public function __construct(
        private readonly NotifyBackInStockAction $notifyBackInStock,
    ) {}

    public function handle(Product $product, ?int $previousStock = null, ?bool $wasActive = null): int
    {
        $product->loadMissing('seller:id,name,company_name,is_active,deleted_at');

        if (! $this->becamePurchasable($product, $previousStock, $wasActive)) {
            return 0;
        }

        return $this->notifyBackInStock->handle($product);
    }

    private function becamePurchasable(Product $product, ?int $previousStock, ?bool $wasActive): bool
    {
        if (! $product->isPurchasableByBuyers()) {
            return false;
        }

        $wasPreviouslyPurchasable = ($wasActive ?? (bool) $product->is_active)
            && ($previousStock ?? (int) $product->stock) > 0;

        return ! $wasPreviouslyPurchasable;
    }
}
