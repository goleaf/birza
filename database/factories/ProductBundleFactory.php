<?php

namespace Database\Factories;

use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductBundle>
 */
class ProductBundleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'seller_id' => Seller::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraph(),
            'status' => ProductBundle::STATUS_DRAFT,
            'discount_type' => null,
            'discount_value' => null,
            'starts_at' => null,
            'ends_at' => null,
            'published_at' => null,
            'image_path' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductBundle::STATUS_ACTIVE,
            'published_at' => now(),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductBundle::STATUS_INACTIVE,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductBundle::STATUS_ARCHIVED,
            'published_at' => null,
        ]);
    }

    public function percentageDiscount(float $value = 10.0): static
    {
        return $this->state(fn (array $attributes): array => [
            'discount_type' => ProductBundle::DISCOUNT_TYPE_PERCENTAGE,
            'discount_value' => $value,
        ]);
    }

    public function fixedDiscount(float $value = 5.0): static
    {
        return $this->state(fn (array $attributes): array => [
            'discount_type' => ProductBundle::DISCOUNT_TYPE_FIXED_AMOUNT,
            'discount_value' => $value,
        ]);
    }

    public function withItems(int $count = 2): static
    {
        return $this->afterCreating(function (ProductBundle $bundle) use ($count): void {
            ProductBundleItem::factory()
                ->count(max(ProductBundle::minimumProducts(), $count))
                ->forBundle($bundle)
                ->create();
        });
    }
}
