<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoProductBundleSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('product_bundles') || ! Schema::hasTable('product_bundle_items')) {
            return;
        }

        $this->ensureDemoDependencies();

        $sellerOne = $this->seller('demo-seller-one@example.com');
        $seller = $this->seller('seller@example.com');

        $this->bundle($sellerOne, 'demo-weekend-fruit-cheese-set', [
            'name' => 'Demo Weekend Fruit and Cheese Set',
            'status' => ProductBundle::STATUS_ACTIVE,
            'discount_type' => ProductBundle::DISCOUNT_TYPE_PERCENTAGE,
            'discount_value' => 12,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'published_at' => now()->subHour(),
        ], [
            'Demo Active Apples' => 2,
            'Demo Changed Price Cheese' => 1,
        ]);

        $this->bundle($seller, 'demo-local-breakfast-set', [
            'name' => 'Demo Local Breakfast Set',
            'status' => ProductBundle::STATUS_ACTIVE,
            'discount_type' => null,
            'discount_value' => null,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeeks(3),
            'published_at' => now()->subHour(),
        ], [
            'Demo Published Product' => 1,
            'Demo Low Stock Yogurt' => 1,
        ]);

        $this->bundle($sellerOne, 'demo-draft-seller-set', [
            'name' => 'Demo Draft Seller Set',
            'status' => ProductBundle::STATUS_DRAFT,
        ], [
            'Demo Active Apples' => 1,
            'Demo Changed Price Cheese' => 1,
        ]);

        $this->bundle($sellerOne, 'demo-archived-seller-set', [
            'name' => 'Demo Archived Seller Set',
            'status' => ProductBundle::STATUS_ARCHIVED,
        ], [
            'Demo Active Apples' => 1,
            'Demo Changed Price Cheese' => 1,
        ]);

        $this->bundle($sellerOne, 'demo-inactive-product-set', [
            'name' => 'Demo Inactive Product Set',
            'status' => ProductBundle::STATUS_INACTIVE,
        ], [
            'Demo Active Apples' => 1,
            'Demo Inactive Honey' => 1,
        ]);

        $this->bundle($sellerOne, 'demo-out-of-stock-set', [
            'name' => 'Demo Out Of Stock Set',
            'status' => ProductBundle::STATUS_INACTIVE,
        ], [
            'Demo Active Apples' => 1,
            'Demo Out Of Stock Milk' => 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, int>  $items
     */
    private function bundle(Seller $seller, string $slug, array $attributes, array $items): ProductBundle
    {
        $bundle = ProductBundle::withTrashed()->firstOrNew(['slug' => $slug]);
        $bundle->forceFill(array_merge([
            'seller_id' => $seller->id,
            'slug' => $slug,
            'description' => 'Demo product bundle for seller and buyer workflows.',
            'discount_type' => null,
            'discount_value' => null,
            'starts_at' => null,
            'ends_at' => null,
            'published_at' => null,
            'image_path' => null,
        ], $attributes));

        if ($bundle->trashed()) {
            $bundle->restore();
        }

        $bundle->save();

        $productIds = collect($items)
            ->keys()
            ->map(fn (string $name): int => $this->product($name)->id)
            ->values();

        $bundle->items()
            ->whereNotIn('product_id', $productIds)
            ->delete();

        collect($items)->each(function (int $quantity, string $productName) use ($bundle): void {
            $product = $this->product($productName);

            $bundle->items()->updateOrCreate([
                'product_id' => $product->id,
            ], [
                'quantity' => $quantity,
                'sort_order' => $bundle->items()->count(),
            ]);
        });

        return $bundle;
    }

    private function seller(string $email): Seller
    {
        return Seller::query()->where('email', $email)->firstOrFail();
    }

    private function product(string $name): Product
    {
        return Product::withTrashed()->where('name', $name)->firstOrFail();
    }

    private function ensureDemoDependencies(): void
    {
        if (! Seller::query()->where('email', 'demo-seller-one@example.com')->exists()) {
            $this->call(DemoUserSeeder::class);
        }

        if (! Product::query()->where('name', 'Demo Active Apples')->exists()) {
            $this->call(DemoCatalogSeeder::class);
        }
    }
}
