<?php

namespace App\Notifications\Marketplace;

use App\Models\Order;

class OrderCreatedNotification extends MarketplaceNotification
{
    public function __construct(public Order $order) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.order.created';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        return [
            'title_key' => 'notifications.orders.created.title',
            'message_key' => 'notifications.orders.created.message',
            'title_params' => ['order' => $this->order->id],
            'message_params' => ['order' => $this->order->id],
            'related_type' => 'order',
            'related_id' => $this->order->id,
            'url' => route('buyer.orders.show', $this->order, false),
            'status' => $this->order->lifecycleStatus()->value,
            'icon' => 'shopping-bag',
        ];
    }
}
