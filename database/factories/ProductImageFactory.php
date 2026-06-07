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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'disk' => 'public',
            'path' => 'products/'.$this->faker->uuid().'.webp',
            'alt_text' => $this->faker->sentence(3),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_primary' => false,
        ];
    }
}
