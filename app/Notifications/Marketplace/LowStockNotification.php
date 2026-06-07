<?php

namespace App\Notifications\Marketplace;

use App\Models\Product;

class LowStockNotification extends MarketplaceNotification
{
    public function __construct(
        public Product $product,
        public string $stockStatus,
        public int $threshold,
    ) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.stock.'.$this->stockStatus;
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        $keyPrefix = $this->stockStatus === 'out'
            ? 'notifications.stock.out'
            : 'notifications.stock.low';

        return [
            'title_key' => $keyPrefix.'.title',
            'message_key' => $keyPrefix.'.message',
            'title_params' => ['product' => $this->product->name],
            'message_params' => [
                'product' => $this->product->name,
                'stock' => (int) $this->product->stock,
                'threshold' => $this->threshold,
            ],
            'related_type' => 'product',
            'related_id' => $this->product->id,
            'url' => route('seller.products.edit', $this->product, false),
            'status' => $this->stockStatus,
            'icon' => $this->stockStatus === 'out' ? 'archive-box-x-mark' : 'exclamation-triangle',
        ];
    }
}
