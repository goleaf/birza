<?php

namespace App\Notifications\Marketplace;

use App\Models\Product;

class ProductRejectedNotification extends MarketplaceNotification
{
    protected bool $sendMail = true;

    public function __construct(
        public Product $product,
        public ?string $reason = null,
    ) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.product.rejected';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        return [
            'title_key' => 'notifications.products.rejected.title',
            'message_key' => filled($this->reason)
                ? 'notifications.products.rejected.message_with_reason'
                : 'notifications.products.rejected.message',
            'title_params' => ['product' => $this->product->name],
            'message_params' => [
                'product' => $this->product->name,
                'reason' => $this->reason,
            ],
            'related_type' => 'product',
            'related_id' => $this->product->id,
            'url' => route('seller.products.edit', $this->product, false),
            'status' => 'rejected',
            'icon' => 'x-circle',
        ];
    }
}
