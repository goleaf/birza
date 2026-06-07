<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartBundleItem;
use App\Models\ProductBundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartBundleItem>
 */
class CartBundleItemFactory extends Factory
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
            'product_bundle_id' => ProductBundle::factory()->active()->withItems(),
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => fn (array $attributes): float => ProductBundle::query()
                ->with('items.product.seller')
                ->findOrFail($attributes['product_bundle_id'])
                ->finalPrice(),
        ];
    }

    public function forBundle(ProductBundle $bundle, ?int $quantity = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_bundle_id' => $bundle->id,
            'quantity' => $quantity ?? 1,
            'unit_price' => $bundle->fresh(['items.product.seller'])?->finalPrice() ?? 0.0,
        ]);
    }
}
