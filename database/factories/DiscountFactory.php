<?php

namespace Database\Factories;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'product_id' => null,
            'category_id' => null,
            'name' => $this->faker->words(3, true),
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => $this->faker->numberBetween(5, 25),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => Discount::STATUS_ACTIVE,
            'usage_limit' => null,
            'used_count' => 0,
            'minimum_order_amount' => null,
        ];
    }

    public function percentage(float $value = 10): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => $value,
        ]);
    }

    public function fixedAmount(float $value = 5): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => Discount::TYPE_FIXED_AMOUNT,
            'value' => $value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Discount::STATUS_INACTIVE,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function notStarted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function usageLimitReached(int $limit = 1): static
    {
        return $this->state(fn (array $attributes): array => [
            'usage_limit' => $limit,
            'used_count' => $limit,
        ]);
    }

    public function minimumOrder(float $amount): static
    {
        return $this->state(fn (array $attributes): array => [
            'minimum_order_amount' => $amount,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes): array => [
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'category_id' => null,
        ]);
    }
}
