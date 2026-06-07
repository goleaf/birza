<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Users\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $admin = Admin::factory();
        $product = Product::factory();

        return [
            'actor_id' => $admin,
            'actor_type' => Admin::class,
            'actor_role' => 'admin',
            'action' => $this->faker->randomElement([
                'product.created',
                'product.price_changed',
                'order.created',
                'order.status_changed',
                'user.blocked',
            ]),
            'auditable_id' => $product,
            'auditable_type' => Product::class,
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'accepted'],
            'metadata' => ['source' => 'factory'],
            'reason' => $this->faker->optional()->sentence(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_at' => $this->faker->dateTimeBetween('-30 days'),
        ];
    }

    public function action(string $action): static
    {
        return $this->state(fn (array $attributes): array => [
            'action' => $action,
        ]);
    }

    public function byAdmin(Admin $admin): static
    {
        return $this->state(fn (array $attributes): array => [
            'actor_id' => $admin->getKey(),
            'actor_type' => $admin::class,
            'actor_role' => 'admin',
        ]);
    }

    public function forAuditable(object $auditable): static
    {
        return $this->state(fn (array $attributes): array => [
            'auditable_id' => method_exists($auditable, 'getKey') ? $auditable->getKey() : null,
            'auditable_type' => $auditable::class,
        ]);
    }
}
