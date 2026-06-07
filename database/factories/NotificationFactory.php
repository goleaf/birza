<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => $this->faker->randomElement([
                'marketplace.order.created',
                'marketplace.order.status_changed',
                'marketplace.product.moderation_required',
            ]),
            'notifiable_type' => Buyer::class,
            'notifiable_id' => Buyer::factory(),
            'data' => [
                'title_key' => 'notifications.demo.title',
                'message_key' => 'notifications.demo.message',
                'title_params' => [],
                'message_params' => [],
                'related_type' => 'system',
                'related_id' => null,
                'url' => null,
                'status' => null,
                'icon' => 'bell',
            ],
            'read_at' => null,
        ];
    }

    public function forNotifiable(Model $notifiable): static
    {
        return $this->state(fn (array $attributes): array => [
            'notifiable_type' => $notifiable::class,
            'notifiable_id' => $notifiable->getKey(),
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => now()->subDays($this->faker->numberBetween(1, 14)),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => null,
        ]);
    }

    public function orderCreated(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'marketplace.order.created',
            'data' => array_merge($attributes['data'] ?? [], [
                'title_key' => 'notifications.orders.created.title',
                'message_key' => 'notifications.orders.created.message',
                'related_type' => 'order',
                'status' => 'pending',
                'icon' => 'shopping-bag',
            ]),
        ]);
    }

    public function orderStatusChanged(string $status = 'processing'): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'marketplace.order.status_changed',
            'data' => array_merge($attributes['data'] ?? [], [
                'title_key' => 'notifications.orders.status_changed.title',
                'message_key' => 'notifications.orders.status_changed.message',
                'related_type' => 'order',
                'status' => $status,
                'icon' => 'truck',
            ]),
        ]);
    }

    public function productModerationRequired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'marketplace.product.moderation_required',
            'data' => array_merge($attributes['data'] ?? [], [
                'title_key' => 'notifications.products.moderation_required.title',
                'message_key' => 'notifications.products.moderation_required.message',
                'related_type' => 'product',
                'status' => 'pending',
                'icon' => 'cube',
            ]),
        ]);
    }
}
