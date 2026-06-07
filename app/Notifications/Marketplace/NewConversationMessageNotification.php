<?php

namespace App\Notifications\Marketplace;

use App\Models\Message;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;

class NewConversationMessageNotification extends MarketplaceNotification
{
    public function __construct(public Message $message) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.message.new';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        $this->message->loadMissing([
            'conversation.product',
            'conversation.order',
            'senderBuyer',
            'senderSeller',
            'senderAdmin',
        ]);

        return [
            'title_key' => 'notifications.messages.new.title',
            'message_key' => 'notifications.messages.new.message',
            'title_params' => [
                'sender' => $this->message->senderLabel(),
            ],
            'message_params' => [
                'sender' => $this->message->senderLabel(),
                'preview' => $this->message->preview(),
            ],
            'related_type' => 'conversation',
            'related_id' => $this->message->conversation_id,
            'url' => $this->urlFor($notifiable),
            'status' => $this->message->conversation?->status?->value,
            'icon' => 'chat-bubble-left-right',
            'preview' => $this->message->preview(),
        ];
    }

    private function urlFor(object $notifiable): string
    {
        if ($notifiable instanceof Buyer) {
            return route('buyer.messages.show', $this->message->conversation_id, false);
        }

        if ($notifiable instanceof Seller) {
            return route('seller.messages.show', $this->message->conversation_id, false);
        }

        return route('admin.messages.show', $this->message->conversation_id, false);
    }
}
