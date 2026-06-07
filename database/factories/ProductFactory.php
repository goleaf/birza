<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $imagePath = 'images/products/factory/'.$this->faker->uuid().'/medium.svg';

        return [
            'name' => $this->faker->words(3, true),
            'category_id' => Category::factory(),
            'seller_id' => Seller::factory(),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'pack_type' => $this->faker->word(),
            'min_order_price' => $this->faker->randomFloat(2, 10, 100),
            'min_order_count' => $this->faker->numberBetween(1, 10),
            'unit' => $this->faker->randomElement(Product::UNITS),
            'is_organic' => $this->faker->boolean(),
            'country_of_origin' => Country::factory(),
            'product_image' => $imagePath,
            'product_additional_image' => null,
            'image_library' => [
                ['uuid' => $this->faker->uuid(), 'path' => $imagePath],
            ],
            'description' => [
                'en' => $this->faker->paragraph(),
                'lt' => $this->faker->paragraph(),
            ],
            'is_active' => true,
            'package_weight' => $this->faker->randomFloat(3, 0.1, 50),
            'price_per_liter' => $this->faker->randomFloat(2, 1, 100),
            'stock' => $this->faker->numberBetween(10, 1000),
            'temperature_conditions_from' => $this->faker->numberBetween(-20, 20),
            'temperature_conditions_to' => $this->faker->numberBetween(0, 30),
            'use_until' => $this->faker->dateTimeBetween('now', '+1 year'),
            'total_shelf_life' => $this->faker->numberBetween(1, 365),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function published(): static
    {
        return $this->active();
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock' => 0,
        ]);
    }

    public function lowStock(int $quantity = 2): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock' => max(1, $quantity),
        ]);
    }

    public function minimumPrice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'price' => 0.01,
            'min_order_price' => 0.01,
            'min_order_count' => 1,
        ]);
    }

    public function highPrice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'price' => 99999.99,
            'min_order_price' => 99999.99,
        ]);
    }

    public function longTitle(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Very long demo marketplace product title for layout wrapping validation across catalog cards and product details',
        ]);
    }

    public function longDescription(): static
    {
        $description = $this->faker->paragraphs(8, true);

        return $this->state(fn (array $attributes): array => [
            'description' => [
                'en' => $description,
                'lt' => $description,
            ],
        ]);
    }

    public function withoutImages(): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_image' => '',
            'product_additional_image' => null,
            'image_library' => [],
        ]);
    }

    public function withLegacyImages(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_image' => 'products/placeholders/'.$this->faker->uuid().'.webp',
            'product_additional_image' => 'products/placeholders/'.$this->faker->uuid().'.webp',
        ]);
    }

    public function withImages(int $count = 1): static
    {
        return $this->afterCreating(function (Product $product) use ($count): void {
            if (! Schema::hasTable('product_images')) {
                return;
            }

            $images = collect(range(0, max(1, $count) - 1))
                ->map(fn (int $index): ProductImage => ProductImage::factory()
                    ->forProductPath($product, $index)
                    ->create())
                ->values();

            $library = $images
                ->map(fn (ProductImage $image): array => $image->toLibraryItem('medium'))
                ->values()
                ->all();

            $product->forceFill([
                'product_image' => $images->first()?->variantPath('medium') ?? $product->product_image,
                'product_additional_image' => $images->get(1)?->variantPath('medium'),
                'image_library' => $library,
            ])->save();
        });
    }

    public function withGallery(int $count = 3): static
    {
        return $this->withImages(max(2, $count));
    }

    public function withReviews(int $count = 3): static
    {
        return $this->afterCreating(function (Product $product) use ($count): void {
            if (! Schema::hasTable('reviews')) {
                return;
            }

            Review::factory()
                ->count($count)
                ->approved()
                ->for($product)
                ->create();
        });
    }

    public function softDeleted(): static
    {
        return $this->afterCreating(function (Product $product): void {
            $product->delete();
        });
    }
}
