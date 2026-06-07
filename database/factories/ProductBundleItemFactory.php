<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductBundleItem>
 */
class ProductBundleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_bundle_id' => ProductBundle::factory(),
            'product_id' => fn (array $attributes): int => Product::factory()
                ->for(ProductBundle::query()->findOrFail($attributes['product_bundle_id'])->seller, 'seller')
                ->create()
                ->id,
            'quantity' => $this->faker->numberBetween(1, 3),
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function forBundle(ProductBundle $bundle, ?Product $product = null, ?int $quantity = null): static
    {
        $product ??= Product::factory()
            ->for($bundle->seller, 'seller')
            ->active()
            ->create([
                'stock' => 20,
                'min_order_count' => 1,
            ]);

        return $this->state(fn (array $attributes): array => [
            'product_bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => $quantity ?? 1,
            'sort_order' => $attributes['sort_order'] ?? 0,
        ]);
    }
}
