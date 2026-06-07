<?php

namespace App\Notifications\Marketplace;

use App\Models\OrderStatusHistory;
use App\Models\Users\Admin;
use App\Models\Users\Seller;

class OrderStatusChangedNotification extends MarketplaceNotification
{
    protected bool $sendMail = true;

    public function __construct(public OrderStatusHistory $history) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.order.status_changed';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        $order = $this->history->order;

        return [
            'title_key' => 'notifications.orders.status_changed.title',
            'message_key' => 'notifications.orders.status_changed.message',
            'title_params' => ['order' => $order->id],
            'message_params' => [
                'order' => $order->id,
                'old' => $this->history->old_status->label(),
                'new' => $this->history->new_status->label(),
            ],
            'related_type' => 'order',
            'related_id' => $order->id,
            'url' => $this->orderUrlFor($notifiable),
            'status' => $this->history->new_status->value,
            'icon' => $this->history->new_status->icon(),
            'reason' => $this->history->reason,
        ];
    }

    private function orderUrlFor(object $notifiable): string
    {
        if ($notifiable instanceof Seller) {
            return route('seller.orders.show', $this->history->order, false);
        }

        if ($notifiable instanceof Admin) {
            return route('admin.orders.show', $this->history->order, false);
        }

        return route('buyer.orders.show', $this->history->order, false);
    }
}
