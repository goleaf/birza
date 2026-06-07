<?php

namespace App\Notifications\Marketplace;

use App\Models\ProductQuestion;

class ProductQuestionCreatedNotification extends MarketplaceNotification
{
    public function __construct(public ProductQuestion $productQuestion) {}

    public function databaseType(object $notifiable): string
    {
        return 'marketplace.product_question.created';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(object $notifiable): array
    {
        $this->productQuestion->loadMissing('product');

        return [
            'title_key' => 'notifications.product_question.created.title',
            'message_key' => 'notifications.product_question.created.message',
            'title_params' => ['product' => $this->productQuestion->product?->name],
            'message_params' => ['product' => $this->productQuestion->product?->name],
            'related_type' => 'product_question',
            'related_id' => $this->productQuestion->id,
            'url' => route('seller.product-questions.index', ['status' => 'pending'], false),
            'status' => $this->productQuestion->status->value,
            'icon' => 'question-mark-circle',
        ];
    }
}
