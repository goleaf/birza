<?php

namespace Database\Seeders\Demo;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $country = $this->country();
        $defaultCategory = $this->defaultCategory();
        $this->category('demo-empty-category', 'Demo Empty Category', 'Demo tuscia kategorija');
        $inactiveCategory = $this->category('demo-inactive-category', 'Demo Inactive Category', 'Demo neaktyvi kategorija', false);

        $this->attachFilterAttributes();

        $seller = $this->seller('seller@example.com');
        $sellerOne = $this->seller('demo-seller-one@example.com');
        $sellerTwo = $this->seller('demo-seller-two@example.com');
        $blockedSeller = $this->seller('demo-blocked-seller@example.com');
        $manyProductsSeller = $this->seller('buyer-seller@example.com');

        $this->product('Demo Active Apples', $sellerOne, $defaultCategory, $country, 12.50, 50);
        $this->product('Demo Published Product', $seller, $defaultCategory, $country, 14.25, 32);
        $this->product('Demo Product Without Image', $seller, $defaultCategory, $country, 8.99, 12, hasImage: false);
        $this->product('Demo Changed Price Cheese', $sellerOne, $defaultCategory, $country, 18.00, 25);
        $this->product('Demo Inactive Honey', $sellerOne, $defaultCategory, $country, 9.00, 15, isActive: false);
        $this->product('Demo Out Of Stock Milk', $sellerOne, $defaultCategory, $country, 7.00, 0);
        $this->product('Demo Low Stock Yogurt', $seller, $defaultCategory, $country, 4.95, 2);
        $this->product('Demo Seller Two Bread', $sellerTwo, $defaultCategory, $country, 6.00, 40);
        $this->product('Demo Blocked Seller Eggs', $blockedSeller, $defaultCategory, $country, 5.00, 20);
        $this->product('Demo Inactive Category Product', $seller, $inactiveCategory, $country, 11.75, 18);
        $this->product('Demo Minimum Price Product', $seller, $defaultCategory, $country, 0.01, 100);
        $this->product('Demo High Price Product', $seller, $defaultCategory, $country, 99999.99, 4);
        $this->product(
            'Demo Very Long Marketplace Product Title For Wrapping And Table Stability Checks',
            $seller,
            $defaultCategory,
            $country,
            21.45,
            16,
            description: str_repeat('Long seeded product description for layout stability checks. ', 20),
        );

        $this->softDeletedProduct($seller, $defaultCategory, $country);
        $this->paginationProducts($manyProductsSeller, $defaultCategory, $country);

    }

    private function country(): Country
    {
        return Country::query()
            ->where('alpha2', 'lt')
            ->first()
            ?? Country::factory()->active()->create([
                'alpha2' => 'lt',
                'region' => 'Europe',
                'country_name' => ['en' => 'Lithuania', 'lt' => 'Lietuva'],
            ]);
    }

    private function defaultCategory(): Category
    {
        return Category::query()
            ->whereNotNull('parent_category_id')
            ->orderBy('id')
            ->first()
            ?? $this->category('demo-food', 'Demo Food', 'Demo maistas');
    }

    private function category(string $slug, string $englishName, string $lithuanianName, bool $isActive = true): Category
    {
        $category = Category::query()
            ->get()
            ->first(fn (Category $category): bool => $category->getTranslation('slug', 'en') === $slug)
            ?? new Category;

        $category->forceFill([
            'category_name' => ['en' => $englishName, 'lt' => $lithuanianName],
            'slug' => ['en' => $slug, 'lt' => $slug],
            'parent_category_id' => null,
            'order' => 999,
            'is_active' => $isActive,
        ]);

        $category->save();

        return $category;
    }

    private function attachFilterAttributes(): void
    {
        /** @var Collection<int, Attribute> $attributes */
        $attributes = Attribute::query()
            ->active()
            ->filterable()
            ->with('values')
            ->orderBy('id')
            ->limit(4)
            ->get();

        if ($attributes->isEmpty()) {
            return;
        }

        Category::query()
            ->whereNotNull('parent_category_id')
            ->select(['id'])
            ->chunkById(100, function (Collection $categories) use ($attributes): void {
                foreach ($categories as $category) {
                    $category->attributes()->syncWithoutDetaching($attributes->modelKeys());
                }
            });
    }

    private function seller(string $email): Seller
    {
        return Seller::query()->where('email', $email)->firstOrFail();
    }

    private function product(
        string $name,
        Seller $seller,
        Category $category,
        Country $country,
        float $price,
        int $stock,
        bool $isActive = true,
        bool $hasImage = true,
        ?string $description = null,
    ): Product {
        $product = Product::withTrashed()->firstOrNew(['name' => $name]);
        $imagePath = $hasImage ? 'images/products/demo/'.Str::slug($name).'/medium.svg' : '';

        $product->forceFill([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'country_of_origin' => $country->id,
            'price' => $price,
            'stock' => $stock,
            'min_order_count' => 1,
            'min_order_price' => $price,
            'unit' => 'kg',
            'pack_type' => 'box',
            'is_organic' => $stock % 2 === 0,
            'description' => [
                'en' => $description ?? $name,
                'lt' => $description ?? $name,
            ],
            'is_active' => $isActive,
            'product_image' => $imagePath,
            'product_additional_image' => null,
            'image_library' => $hasImage ? [['uuid' => Str::slug($name), 'path' => $imagePath]] : [],
            'package_weight' => 1.000,
            'price_per_liter' => null,
            'temperature_conditions_from' => 2,
            'temperature_conditions_to' => 6,
            'use_until' => now()->addDays(30),
            'total_shelf_life' => 30,
        ]);

        if ($product->trashed()) {
            $product->restore();
        }

        $product->save();

        return $product;
    }

    private function softDeletedProduct(Seller $seller, Category $category, Country $country): void
    {
        $product = $this->product('Demo Soft Deleted Product', $seller, $category, $country, 13.75, 9);
        $product->delete();
    }

    private function paginationProducts(Seller $seller, Category $category, Country $country): void
    {
        for ($index = 1; $index <= 25; $index++) {
            $this->product(
                sprintf('Demo Pagination Product %02d', $index),
                $seller,
                $category,
                $country,
                3.50 + $index,
                10 + $index,
            );
        }
    }
}
