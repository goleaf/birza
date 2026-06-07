<?php

namespace App\Notifications\Marketplace;

use App\Models\ProductQuestion;
use Illuminate\Notifications\AnonymousNotifiable;

class ProductQuestionRejectedNotification extends MarketplaceNotification
{
    protected bool $sendMail = false;

    public function __construct(public ProductQuestion $productQuestion) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return parent::via($notifiable);
    }

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.product_question.rejected';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        $this->productQuestion->loadMissing('product');

        return [
            'title_key' => 'notifications.product_question.rejected.title',
            'message_key' => filled($this->productQuestion->moderation_reason)
                ? 'notifications.product_question.rejected.message_with_reason'
                : 'notifications.product_question.rejected.message',
            'title_params' => ['product' => $this->productQuestion->product?->name],
            'message_params' => [
                'product' => $this->productQuestion->product?->name,
                'reason' => $this->productQuestion->moderation_reason,
            ],
            'related_type' => 'product_question',
            'related_id' => $this->productQuestion->id,
            'url' => route('buyer.products.show', $this->productQuestion->product_id, false).'#product-questions',
            'status' => $this->productQuestion->status->value,
            'icon' => 'x-circle',
        ];
    }
}
