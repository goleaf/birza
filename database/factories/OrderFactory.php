<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_total' => $this->faker->randomFloat(2, 10, 1000),
            'buyer_id' => Buyer::factory(),
            'payment_method' => $this->faker->randomElement(['card', 'bank_transfer', 'cash']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'status' => Order::STATUS['PENDING'],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS['PENDING'],
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS['PAID'],
        ]);
    }
}

