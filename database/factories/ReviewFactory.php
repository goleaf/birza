<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'is_approved' => $this->faker->boolean(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => true,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => false,
        ]);
    }

    public function withoutComment(): static
    {
        return $this->state(fn (array $attributes): array => [
            'body' => null,
        ]);
    }

    public function fiveStars(): static
    {
        return $this->state(fn (array $attributes): array => [
            'rating' => 5,
        ]);
    }

    public function oneStar(): static
    {
        return $this->state(fn (array $attributes): array => [
            'rating' => 1,
        ]);
    }

    public function softDeleted(): static
    {
        return $this->afterCreating(function (Review $review): void {
            $review->delete();
        });
    }
}
