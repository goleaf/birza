<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => Buyer::factory(),
            'guest_token' => null,
            'status' => Cart::STATUS_ACTIVE,
        ];
    }

    public function guest(?string $token = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
            'guest_token' => $token ?? $this->faker->uuid(),
            'status' => Cart::STATUS_ACTIVE,
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Cart::STATUS_CONVERTED,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Cart::STATUS_ACTIVE,
        ]);
    }

    public function empty(): static
    {
        return $this;
    }

    public function withItems(int $count = 3): static
    {
        return $this->afterCreating(function (Cart $cart) use ($count): void {
            CartItem::factory()
                ->count($count)
                ->for($cart)
                ->create();
        });
    }
}
