<?php

namespace App\Actions\Notifications;

use App\Models\Product;
use App\Notifications\Marketplace\LowStockNotification;

class SendStockThresholdNotificationAction
{
    public function __construct(
        private readonly SendMarketplaceNotificationAction $sendNotification,
    ) {}

    public function handle(Product $product, ?int $previousStock = null): void
    {
        $product->loadMissing('seller');

        if (! $product->seller) {
            return;
        }

        $threshold = max(0, (int) config('notifications.low_stock_threshold', 5));
        $stock = (int) $product->stock;

        if ($stock > $threshold) {
            return;
        }

        $stockStatus = $stock <= 0 ? 'out' : 'low';

        if (! $this->crossedThreshold($stockStatus, $stock, $threshold, $previousStock)) {
            return;
        }

        if ($this->alreadyNotified($product, $stockStatus)) {
            return;
        }

        $this->sendNotification->handle(
            $product->seller,
            new LowStockNotification($product, $stockStatus, $threshold),
        );
    }

    private function crossedThreshold(string $stockStatus, int $stock, int $threshold, ?int $previousStock): bool
    {
        if ($previousStock === null) {
            return true;
        }

        if ($stockStatus === 'out') {
            return $previousStock > 0 && $stock <= 0;
        }

        return $previousStock > $threshold && $stock > 0 && $stock <= $threshold;
    }

    private function alreadyNotified(Product $product, string $stockStatus): bool
    {
        return $product->seller->notifications()
            ->where('type', 'marketplace.stock.'.$stockStatus)
            ->where('data->related_type', 'product')
            ->where('data->related_id', $product->id)
            ->where('data->status', $stockStatus)
            ->exists();
    }
}
