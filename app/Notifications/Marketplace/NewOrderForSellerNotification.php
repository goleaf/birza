<?php

namespace App\Notifications\Marketplace;

use App\Models\Order;

class NewOrderForSellerNotification extends MarketplaceNotification
{
    protected bool $sendMail = true;

    public function __construct(public Order $order) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.order.new_for_seller';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        return [
            'title_key' => 'notifications.orders.new_for_seller.title',
            'message_key' => 'notifications.orders.new_for_seller.message',
            'title_params' => ['order' => $this->order->id],
            'message_params' => ['order' => $this->order->id],
            'related_type' => 'order',
            'related_id' => $this->order->id,
            'url' => route('seller.orders.show', $this->order, false),
            'status' => $this->order->lifecycleStatus()->value,
            'icon' => 'shopping-bag',
        ];
    }
}
