<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'old_status' => OrderStatus::Pending,
            'new_status' => OrderStatus::Accepted,
            'changed_by_user_id' => null,
            'changed_by_role' => OrderStatusActorRole::System,
            'reason' => $this->faker->optional()->sentence(),
            'note' => $this->faker->optional()->sentence(),
        ];
    }

    public function transition(OrderStatus $oldStatus, OrderStatus $newStatus): static
    {
        return $this->state(fn (array $attributes): array => [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    public function byBuyer(?int $userId = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'changed_by_user_id' => $userId,
            'changed_by_role' => OrderStatusActorRole::Buyer,
        ]);
    }

    public function bySeller(?int $userId = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'changed_by_user_id' => $userId,
            'changed_by_role' => OrderStatusActorRole::Seller,
        ]);
    }

    public function byAdmin(?int $userId = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'changed_by_user_id' => $userId,
            'changed_by_role' => OrderStatusActorRole::Admin,
        ]);
    }

    public function bySystem(): static
    {
        return $this->state(fn (array $attributes): array => [
            'changed_by_user_id' => null,
            'changed_by_role' => OrderStatusActorRole::System,
        ]);
    }
}
