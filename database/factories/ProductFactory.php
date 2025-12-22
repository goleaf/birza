<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Users\Seller;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
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
            'product_image' => $this->faker->uuid() . '.webp',
            'product_additional_image' => $this->faker->uuid() . '.webp',
            'description' => [
                'en' => $this->faker->paragraph(),
                'lt' => $this->faker->paragraph(),
            ],
            'is_active' => true,
            'package_weight' => $this->faker->randomFloat(3, 0.1, 50),
            'price_per_liter' => $this->faker->randomFloat(2, 1, 100),
            'stock' => $this->faker->numberBetween(0, 1000),
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
}

