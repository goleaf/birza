<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = $this->faker->uuid();
        $basePath = 'images/products/'.$this->faker->numberBetween(1, 100).'/'.$uuid;

        return [
            'product_id' => Product::factory(),
            'type' => 'product',
            'disk' => 'public',
            'path' => $basePath.'/medium.svg',
            'original_path' => $basePath.'/original.svg',
            'variants' => [
                'thumb' => ['path' => $basePath.'/thumb.svg', 'width' => 160, 'height' => 120],
                'medium' => ['path' => $basePath.'/medium.svg', 'width' => 640, 'height' => 480],
                'large' => ['path' => $basePath.'/large.svg', 'width' => 1200, 'height' => 900],
            ],
            'original_name' => $this->faker->slug().'.svg',
            'mime_type' => 'image/svg+xml',
            'size' => $this->faker->numberBetween(10_000, 500_000),
            'width' => 640,
            'height' => 480,
            'alt_text' => $this->faker->sentence(3),
            'caption' => null,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'sort_order' => 0,
            'is_primary' => true,
        ]);
    }

    public function gallery(int $sortOrder = 1): static
    {
        return $this->state(fn (array $attributes): array => [
            'sort_order' => $sortOrder,
            'is_primary' => false,
        ]);
    }

    public function type(string $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }

    public function forProductPath(Product $product, int $sortOrder = 0): static
    {
        $basePath = sprintf('images/products/%d/demo-%02d', $product->getKey(), $sortOrder + 1);

        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->getKey(),
            'path' => $basePath.'/medium.svg',
            'original_path' => $basePath.'/original.svg',
            'variants' => [
                'thumb' => ['path' => $basePath.'/thumb.svg', 'width' => 160, 'height' => 120],
                'medium' => ['path' => $basePath.'/medium.svg', 'width' => 640, 'height' => 480],
                'large' => ['path' => $basePath.'/large.svg', 'width' => 1200, 'height' => 900],
            ],
            'sort_order' => $sortOrder,
            'is_primary' => $sortOrder === 0,
        ]);
    }
}
