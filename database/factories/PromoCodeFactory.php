<?php

namespace Database\Factories;

use App\Models\PromoCode;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromoCode>
 */
class PromoCodeFactory extends Factory
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
            'code' => strtoupper($this->faker->unique()->bothify('PROMO###??')),
            'type' => PromoCode::TYPE_PERCENTAGE,
            'value' => $this->faker->numberBetween(5, 25),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => PromoCode::STATUS_ACTIVE,
            'usage_limit' => null,
            'used_count' => 0,
            'per_user_limit' => 1,
            'minimum_order_amount' => null,
        ];
    }

    public function code(string $code): static
    {
        return $this->state(fn (array $attributes): array => [
            'code' => PromoCode::normalizeCode($code),
        ]);
    }

    public function percentage(float $value = 10): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => PromoCode::TYPE_PERCENTAGE,
            'value' => $value,
        ]);
    }

    public function fixedAmount(float $value = 5): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => PromoCode::TYPE_FIXED_AMOUNT,
            'value' => $value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PromoCode::STATUS_INACTIVE,
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
}
