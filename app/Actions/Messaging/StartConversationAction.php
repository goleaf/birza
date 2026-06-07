<?php

namespace App\Actions\Messaging;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class StartConversationAction
{
    public function __construct(
        private readonly RecordMessagingAuditAction $audit,
    ) {}

    public function forProduct(Buyer $buyer, Product $product): Conversation
    {
        $product->loadMissing('seller:id,user_id,name,email,company_name,is_active,is_verified');

        Gate::forUser($buyer)->authorize('createFromProduct', [Conversation::class, $product]);

        return $this->handle(
            buyer: $buyer,
            seller: $product->seller,
            actor: $buyer,
            product: $product,
            source: 'product_detail',
        );
    }

    public function forOrder(Authenticatable $actor, Order $order, Seller $seller): Conversation
    {
        Gate::forUser($actor)->authorize('createFromOrder', [Conversation::class, $order, $seller]);

        $buyer = $order->buyer()->firstOrFail();

        return $this->handle(
            buyer: $buyer,
            seller: $seller,
            actor: $actor,
            order: $order,
            source: 'order_detail',
        );
    }

    public function handle(
        Buyer $buyer,
        Seller $seller,
        ?Authenticatable $actor = null,
        ?Product $product = null,
        ?Order $order = null,
        string $source = 'manual',
    ): Conversation {
        $attributes = [
            'buyer_id' => $buyer->getKey(),
            'seller_id' => $seller->getKey(),
            'product_id' => $product?->getKey(),
            'order_id' => $order?->getKey(),
        ];

        $conversation = Conversation::query()->firstOrNew($attributes);
        $wasRecentlyCreated = ! $conversation->exists;

        if ($wasRecentlyCreated) {
            $conversation->forceFill(array_merge($attributes, [
                'status' => ConversationStatus::Active,
                'last_message_at' => null,
            ]));
        }

        $this->clearArchiveForActor($conversation, $actor);
        $conversation->save();

        if ($wasRecentlyCreated) {
            $this->audit->conversationStarted($actor, $conversation, $source);
        }

        return $conversation->refresh();
    }

    private function clearArchiveForActor(Conversation $conversation, ?Authenticatable $actor): void
    {
        if ($actor instanceof Buyer) {
            $conversation->buyer_archived_at = null;
        }

        if ($actor instanceof Seller) {
            $conversation->seller_archived_at = null;
        }
    }
}
