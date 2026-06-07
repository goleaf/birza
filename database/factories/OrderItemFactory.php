<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 1, 100);
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'seller_id' => fn (array $attributes): int => $this->productFor($attributes)->seller_id,
            'product_title_snapshot' => fn (array $attributes): string => $this->productFor($attributes)->name ?? 'Deleted product',
            'product_price_snapshot' => $unitPrice,
            'seller_name_snapshot' => fn (array $attributes): ?string => $this->productFor($attributes)->seller?->company_name,
        ];
    }

    public function forProduct(Product $product, ?int $quantity = null): static
    {
        $quantity ??= $this->faker->numberBetween(1, 5);
        $unitPrice = (float) $product->price;

        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->getKey(),
            'seller_id' => $product->seller_id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'product_title_snapshot' => $product->name ?? 'Deleted product',
            'product_price_snapshot' => $unitPrice,
            'seller_name_snapshot' => $product->seller?->company_name,
        ]);
    }

    public function softDeletedProductSnapshot(): static
    {
        return $this->afterCreating(function (OrderItem $orderItem): void {
            $orderItem->product?->delete();
        });
    }

    private function productFor(array $attributes): Product
    {
        return Product::query()
            ->with('seller')
            ->findOrFail($attributes['product_id']);
    }
}
