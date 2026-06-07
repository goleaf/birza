<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => fn (array $attributes): float => (float) Product::query()
                ->findOrFail($attributes['product_id'])
                ->price,
        ];
    }

    public function forProduct(Product $product, ?int $quantity = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->getKey(),
            'quantity' => $quantity ?? $this->faker->numberBetween(1, 5),
            'unit_price' => (float) $product->price,
        ]);
    }

    public function priceChanged(float $previousPrice): static
    {
        return $this->state(fn (array $attributes): array => [
            'unit_price' => $previousPrice,
        ]);
    }

    public function unavailableProduct(): static
    {
        return $this->forProduct(
            Product::factory()
                ->inactive()
                ->create()
        );
    }

    public function outOfStockProduct(): static
    {
        return $this->forProduct(
            Product::factory()
                ->outOfStock()
                ->create()
        );
    }
}
