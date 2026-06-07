<?php

namespace Database\Factories;

use App\Models\AdminAction;
use App\Models\Users\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminAction>
 */
class AdminActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_user_id' => Admin::factory(),
            'actor_role' => 'admin',
            'action' => fake()->randomElement([
                'product.deleted',
                'product.restored',
                'seller.approved',
                'buyer.credit_adjusted',
                'settings.updated',
            ]),
            'entity_type' => null,
            'entity_id' => null,
            'old_values' => null,
            'new_values' => ['status' => 'updated'],
            'metadata' => ['source' => 'factory'],
            'reason' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
