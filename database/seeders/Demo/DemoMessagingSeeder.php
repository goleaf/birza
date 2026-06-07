<?php

namespace Database\Seeders\Demo;

use App\Enums\ConversationStatus;
use App\Enums\MessageSenderRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoMessagingSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('conversations') || ! Schema::hasTable('messages')) {
            return;
        }

        $buyer = Buyer::query()->where('email', 'buyer@example.com')->first();
        $ordersBuyer = Buyer::query()->where('email', 'demo-orders-buyer@example.com')->first();
        $emptyBuyer = Buyer::query()->where('email', 'demo-empty-buyer@example.com')->first();
        $seller = Seller::query()->where('email', 'seller@example.com')->first();
        $sellerOne = Seller::query()->where('email', 'demo-seller-one@example.com')->first();
        $sellerTwo = Seller::query()->where('email', 'demo-seller-two@example.com')->first();

        if (! $buyer instanceof Buyer || ! $seller instanceof Seller || ! $sellerOne instanceof Seller || ! $sellerTwo instanceof Seller) {
            return;
        }

        $product = Product::query()
            ->with('seller')
            ->where('name', 'Demo Active Apples')
            ->first();

        if ($product instanceof Product && $product->seller instanceof Seller) {
            $this->productConversation($buyer, $product->seller, $product);
        }

        if ($ordersBuyer instanceof Buyer) {
            $this->orderConversation($ordersBuyer, $sellerTwo);
        }

        if ($emptyBuyer instanceof Buyer) {
            $this->emptyBuyerConversationSeedMarker($emptyBuyer);
        }

        $this->closedConversation($buyer, $sellerOne);
        $this->archivedConversation($buyer, $sellerTwo);
        $this->paginationConversations($buyer, $sellerOne);
    }

    private function productConversation(Buyer $buyer, Seller $seller, Product $product): void
    {
        $conversation = $this->conversation([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'order_id' => null,
        ], ConversationStatus::Active);

        $this->replaceDemoMessages($conversation, [
            [MessageSenderRole::Buyer, $buyer->id, 'Hello, can you confirm whether these apples are packed in 5 kg boxes?', true, 5],
            [MessageSenderRole::Seller, $seller->id, 'Yes, we can pack them in 5 kg boxes and prepare the order tomorrow morning.', false, 4],
        ]);
    }

    private function orderConversation(Buyer $buyer, Seller $seller): void
    {
        $order = Order::query()
            ->where('buyer_id', $buyer->id)
            ->whereHas('items', fn ($query) => $query->where('seller_id', $seller->id))
            ->orderByDesc('id')
            ->first();

        if (! $order instanceof Order) {
            return;
        }

        $conversation = $this->conversation([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => null,
            'order_id' => $order->id,
        ], ConversationStatus::Active);

        $this->replaceDemoMessages($conversation, [
            [MessageSenderRole::Buyer, $buyer->id, 'Could you add delivery details for this order?', true, 3],
            [MessageSenderRole::Seller, $seller->id, 'The courier pickup is planned for Friday before noon.', false, 2],
        ]);
    }

    private function closedConversation(Buyer $buyer, Seller $seller): void
    {
        $conversation = $this->conversation([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => null,
            'order_id' => null,
        ], ConversationStatus::Closed);

        $this->replaceDemoMessages($conversation, [
            [MessageSenderRole::Buyer, $buyer->id, 'Thanks, this support question is resolved.', true, 20],
            [MessageSenderRole::Seller, $seller->id, 'Closing this conversation now.', true, 19],
        ]);
    }

    private function archivedConversation(Buyer $buyer, Seller $seller): void
    {
        $conversation = $this->conversation([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'product_id' => null,
            'order_id' => null,
        ], ConversationStatus::Active);

        $conversation->forceFill([
            'buyer_archived_at' => now()->subDays(1),
        ])->save();

        $this->replaceDemoMessages($conversation, [
            [MessageSenderRole::Seller, $seller->id, 'Archived demo conversation for inbox filtering.', true, 12],
        ]);
    }

    private function paginationConversations(Buyer $buyer, Seller $seller): void
    {
        Product::query()
            ->where('seller_id', $seller->id)
            ->where('name', 'like', 'Demo Pagination Product%')
            ->orderBy('id')
            ->limit(15)
            ->get(['id', 'seller_id', 'name'])
            ->each(function (Product $product, int $index) use ($buyer, $seller): void {
                $conversation = $this->conversation([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                    'product_id' => $product->id,
                    'order_id' => null,
                ], ConversationStatus::Active);

                $this->replaceDemoMessages($conversation, [
                    [MessageSenderRole::Buyer, $buyer->id, 'Demo pagination question for '.$product->name.'.', true, 30 + $index],
                ]);
            });
    }

    private function emptyBuyerConversationSeedMarker(Buyer $buyer): void
    {
        Conversation::query()
            ->where('buyer_id', $buyer->id)
            ->whereHas('messages', fn ($query) => $query->where('metadata->source', 'demo_seeder'))
            ->get(['id'])
            ->each(function (Conversation $conversation): void {
                $conversation->messages()->withTrashed()->where('metadata->source', 'demo_seeder')->forceDelete();
                $conversation->forceDelete();
            });
    }

    /**
     * @param  array{buyer_id: int, seller_id: int, product_id: int|null, order_id: int|null}  $attributes
     */
    private function conversation(array $attributes, ConversationStatus $status): Conversation
    {
        $conversation = Conversation::query()->firstOrNew($attributes);
        $conversation->forceFill(array_merge($attributes, [
            'status' => $status,
            'last_message_at' => null,
        ]))->save();

        return $conversation->refresh();
    }

    /**
     * @param  list<array{0: MessageSenderRole, 1: int, 2: string, 3: bool, 4: int}>  $messages
     */
    private function replaceDemoMessages(Conversation $conversation, array $messages): void
    {
        $conversation->messages()
            ->withTrashed()
            ->where('metadata->source', 'demo_seeder')
            ->forceDelete();

        $latestMessageAt = null;

        foreach ($messages as [$senderRole, $senderId, $body, $read, $daysAgo]) {
            $createdAt = now()->subDays($daysAgo);

            $message = Message::query()->forceCreate([
                'conversation_id' => $conversation->id,
                'sender_role' => $senderRole,
                'sender_id' => $senderId,
                'body' => $body,
                'read_at' => $read ? $createdAt->copy()->addHours(1) : null,
                'metadata' => ['source' => 'demo_seeder'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $latestMessageAt = $message->created_at;
        }

        $conversation->forceFill([
            'last_message_at' => $latestMessageAt,
        ])->save();
    }
}
