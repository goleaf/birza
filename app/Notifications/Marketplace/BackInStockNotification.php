<?php

namespace App\Notifications\Marketplace;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BackInStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
    ) {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.stock_alert.back_in_stock';
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->product->loadMissing('seller:id,name,company_name');

        return [
            'title_key' => 'notifications.stock_alert.back_in_stock.title',
            'message_key' => 'notifications.stock_alert.back_in_stock.message',
            'title_params' => [
                'product' => $this->product->name,
            ],
            'message_params' => [
                'product' => $this->product->name,
                'seller' => $this->product->seller?->company_name ?: $this->product->seller?->name,
                'price' => number_format((float) $this->product->price, 2),
            ],
            'related_type' => 'product',
            'related_id' => $this->product->id,
            'url' => route('buyer.products.show', $this->product, false),
            'status' => 'available',
            'icon' => 'bell',
        ];
    }
}
