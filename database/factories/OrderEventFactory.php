<?php

namespace Database\Factories;

use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Order;
use App\Models\OrderEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderEvent>
 */
class OrderEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventType = $this->faker->randomElement(OrderEventType::cases());

        return [
            'order_id' => Order::factory(),
            'actor_id' => null,
            'actor_role' => OrderStatusActorRole::System,
            'event_type' => $eventType,
            'old_status' => null,
            'new_status' => null,
            'title_key' => $eventType->titleKey(),
            'description_key' => $eventType->descriptionKey(),
            'public_note' => $this->faker->optional()->sentence(),
            'internal_note' => null,
            'metadata' => [],
        ];
    }

    public function type(OrderEventType $eventType): static
    {
        return $this->state(fn (array $attributes): array => [
            'event_type' => $eventType,
            'title_key' => $eventType->titleKey(),
            'description_key' => $eventType->descriptionKey(),
        ]);
    }

    public function statusTransition(OrderStatus $oldStatus, OrderStatus $newStatus): static
    {
        $eventType = OrderEventType::fromOrderStatus($newStatus);

        return $this->type($eventType)->state(fn (array $attributes): array => [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function byBuyer(?int $actorId = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'actor_id' => $actorId,
            'actor_role' => OrderStatusActorRole::Buyer,
        ]);
    }

    public function bySeller(?int $actorId = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'actor_id' => $actorId,
            'actor_role' => OrderStatusActorRole::Seller,
        ]);
    }

    public function byAdmin(?int $actorId = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'actor_id' => $actorId,
            'actor_role' => OrderStatusActorRole::Admin,
        ]);
    }

    public function internal(?string $note = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'public_note' => null,
            'internal_note' => $note ?? $this->faker->sentence(),
        ]);
    }
}
