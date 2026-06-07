<?php

namespace Database\Factories;

use App\Enums\MessageSenderRole;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_role' => MessageSenderRole::Buyer,
            'sender_id' => fn (array $attributes): int => $this->conversationFor($attributes)->buyer_id,
            'body' => $this->faker->sentence(12),
            'read_at' => null,
            'edited_at' => null,
            'metadata' => null,
        ];
    }

    public function forConversation(Conversation $conversation): static
    {
        return $this->state(fn (array $attributes): array => [
            'conversation_id' => $conversation->getKey(),
            'sender_role' => MessageSenderRole::Buyer,
            'sender_id' => $conversation->buyer_id,
        ]);
    }

    public function fromBuyer(?Conversation $conversation = null): static
    {
        return $this->state(function (array $attributes) use ($conversation): array {
            $resolvedConversation = $conversation ?? $this->conversationFor($attributes);

            return [
                'conversation_id' => $resolvedConversation->getKey(),
                'sender_role' => MessageSenderRole::Buyer,
                'sender_id' => $resolvedConversation->buyer_id,
            ];
        });
    }

    public function fromSeller(?Conversation $conversation = null): static
    {
        return $this->state(function (array $attributes) use ($conversation): array {
            $resolvedConversation = $conversation ?? $this->conversationFor($attributes);

            return [
                'conversation_id' => $resolvedConversation->getKey(),
                'sender_role' => MessageSenderRole::Seller,
                'sender_id' => $resolvedConversation->seller_id,
            ];
        });
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => null,
        ]);
    }

    private function conversationFor(array $attributes): Conversation
    {
        return Conversation::query()
            ->select(['id', 'buyer_id', 'seller_id'])
            ->findOrFail($attributes['conversation_id']);
    }
}
