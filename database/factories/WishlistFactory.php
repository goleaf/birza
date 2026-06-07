<?php

namespace Database\Factories;

use App\Models\Users\Buyer;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Wishlist>
 */
class WishlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'buyer_id' => Buyer::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->optional()->sentence(),
            'is_default' => false,
            'is_private' => true,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => __('wishlists.default_name'),
            'slug' => 'default-wishlist',
            'is_default' => true,
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_private' => false,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_private' => true,
        ]);
    }

    public function withItems(int $count = 3): static
    {
        return $this->afterCreating(function (Wishlist $wishlist) use ($count): void {
            WishlistItem::factory()
                ->count($count)
                ->for($wishlist)
                ->create();
        });
    }
}
