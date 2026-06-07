<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderBundle;
use App\Models\ProductBundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderBundle>
 */
class OrderBundleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bundle = ProductBundle::factory()
            ->active()
            ->withItems()
            ->create();
        $basePrice = $bundle->basePrice();
        $discountAmount = $bundle->discountAmount($basePrice);

        return [
            'order_id' => Order::factory(),
            'product_bundle_id' => $bundle->id,
            'seller_id' => $bundle->seller_id,
            'bundle_name_snapshot' => $bundle->name,
            'quantity' => 1,
            'base_price' => $basePrice,
            'discount_type' => $bundle->discount_type,
            'discount_value' => $bundle->discount_value,
            'discount_amount' => $discountAmount,
            'final_price' => $bundle->finalPrice(),
            'products_snapshot' => $bundle->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'title' => $item->product?->name,
                'unit_price' => (float) ($item->product?->price ?? 0),
                'quantity' => $item->quantity,
            ])->values()->all(),
        ];
    }

    public function forBundle(ProductBundle $bundle, ?int $quantity = null): static
    {
        $bundle = $bundle->fresh(['items.product']) ?? $bundle;
        $bundleQuantity = $quantity ?? 1;
        $basePrice = $bundle->basePrice() * $bundleQuantity;
        $discountAmount = $bundle->discountAmount($bundle->basePrice()) * $bundleQuantity;

        return $this->state(fn (array $attributes): array => [
            'product_bundle_id' => $bundle->id,
            'seller_id' => $bundle->seller_id,
            'bundle_name_snapshot' => $bundle->name,
            'quantity' => $bundleQuantity,
            'base_price' => $basePrice,
            'discount_type' => $bundle->discount_type,
            'discount_value' => $bundle->discount_value,
            'discount_amount' => $discountAmount,
            'final_price' => max(0, $basePrice - $discountAmount),
            'products_snapshot' => $bundle->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'title' => $item->product?->name,
                'unit_price' => (float) ($item->product?->price ?? 0),
                'quantity' => (int) $item->quantity * $bundleQuantity,
            ])->values()->all(),
        ]);
    }
}
