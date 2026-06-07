<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['system', 'order', 'catalog']),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'data' => ['reference' => $this->faker->uuid()],
            'read_at' => null,
        ];
    }
}
