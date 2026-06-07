<?php

namespace App\Notifications\Marketplace;

use App\Models\Product;

class ProductModerationRequiredNotification extends MarketplaceNotification
{
    public function __construct(public Product $product) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.product.moderation_required';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        return [
            'title_key' => 'notifications.products.moderation_required.title',
            'message_key' => 'notifications.products.moderation_required.message',
            'title_params' => ['product' => $this->product->name],
            'message_params' => ['product' => $this->product->name],
            'related_type' => 'product',
            'related_id' => $this->product->id,
            'url' => route('admin.products.show', $this->product, false),
            'status' => 'pending',
            'icon' => 'shield-exclamation',
        ];
    }
}
