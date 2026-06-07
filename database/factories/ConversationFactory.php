<?php

namespace Database\Factories;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'buyer_id' => Buyer::factory(),
            'seller_id' => Seller::factory(),
            'product_id' => null,
            'order_id' => null,
            'status' => ConversationStatus::Active,
            'last_message_at' => null,
            'buyer_archived_at' => null,
            'seller_archived_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ConversationStatus::Active,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ConversationStatus::Closed,
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ConversationStatus::Blocked,
        ]);
    }

    public function archivedByBuyer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'buyer_archived_at' => now(),
        ]);
    }

    public function archivedBySeller(): static
    {
        return $this->state(fn (array $attributes): array => [
            'seller_archived_at' => now(),
        ]);
    }

    public function forProduct(Product $product, ?Buyer $buyer = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'buyer_id' => $buyer?->getKey() ?? ($attributes['buyer_id'] ?? Buyer::factory()),
            'seller_id' => $product->seller_id,
            'product_id' => $product->getKey(),
            'order_id' => null,
        ]);
    }

    public function forOrder(Order $order, Seller $seller): static
    {
        return $this->state(fn (array $attributes): array => [
            'buyer_id' => $order->buyer_id,
            'seller_id' => $seller->getKey(),
            'product_id' => null,
            'order_id' => $order->getKey(),
        ]);
    }

    public function withMessages(int $count = 3): static
    {
        return $this->afterCreating(function (Conversation $conversation) use ($count): void {
            Message::factory()
                ->count($count)
                ->forConversation($conversation)
                ->create();

            $conversation->forceFill([
                'last_message_at' => $conversation->messages()->latest('created_at')->value('created_at'),
            ])->save();
        });
    }
}
