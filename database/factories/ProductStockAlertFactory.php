<?php

namespace Database\Factories;

use App\Enums\ProductStockAlertStatus;
use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStockAlert>
 */
class ProductStockAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->outOfStock(),
            'buyer_id' => Buyer::factory(),
            'status' => ProductStockAlertStatus::Active,
            'notified_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStockAlertStatus::Active,
            'notified_at' => null,
        ]);
    }

    public function notified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStockAlertStatus::Notified,
            'notified_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStockAlertStatus::Cancelled,
            'notified_at' => null,
        ]);
    }
}
