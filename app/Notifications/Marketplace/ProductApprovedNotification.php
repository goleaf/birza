<?php

namespace App\Notifications\Marketplace;

use App\Models\Product;

class ProductApprovedNotification extends MarketplaceNotification
{
    public function __construct(public Product $product) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.product.approved';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        return [
            'title_key' => 'notifications.products.approved.title',
            'message_key' => 'notifications.products.approved.message',
            'title_params' => ['product' => $this->product->name],
            'message_params' => ['product' => $this->product->name],
            'related_type' => 'product',
            'related_id' => $this->product->id,
            'url' => route('seller.products.edit', $this->product, false),
            'status' => 'approved',
            'icon' => 'check-badge',
        ];
    }
}
