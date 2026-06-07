<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\SellerTransaction;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerTransactionFactory extends Factory
{
    protected $model = SellerTransaction::class;

    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'order_id' => Order::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['addition', 'deduction', 'refund']),
            'description' => $this->faker->sentence(),
        ];
    }

    public function addition(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'addition',
        ]);
    }

    public function deduction(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'deduction',
        ]);
    }

    public function refund(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'refund',
        ]);
    }

    public function forSellerOrder(Seller $seller, Order $order): static
    {
        return $this->state(fn (array $attributes): array => [
            'seller_id' => $seller->getKey(),
            'order_id' => $order->getKey(),
        ]);
    }
}
