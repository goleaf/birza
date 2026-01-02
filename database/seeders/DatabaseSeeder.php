<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Country;
use App\Models\GlobalSettings;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->resetTables();

        Admin::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@birza.test',
            'password' => 'password',
            'is_active' => true,
        ]);

        GlobalSettings::factory()->create();

        $countries = Country::factory()
            ->active()
            ->count(20)
            ->create();

        [$mainCategories, $subcategories] = $this->seedCategories();

        [$sellers, $categorySellerMap] = $this->seedUsers($subcategories);

        [$attributeValues, $categoryAttributeMap] = $this->seedAttributes($subcategories);

        $products = $this->seedProducts($subcategories, $countries, $categorySellerMap);

        $this->attachAttributeValuesToProducts($products, $attributeValues, $categoryAttributeMap);
    }

    private function resetTables(): void
    {
        $tables = [
            'product_attribute_value',
            'attribute_product',
            'product_attribute',
            'category_attribute',
            'seller_categories',
            'attribute_values',
            'attributes',
            'products',
            'categories',
            'countries',
            'users_sellers',
            'users_buyers',
            'users_admins',
            'global_settings',
            'orders',
            'order_items',
            'carts',
            'activities',
            'buyer_credit_history',
            'credit_attachments',
            'seller_transactions',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    private function seedCategories(): array
    {
        $mainCategories = collect(range(1, 6))->map(function (int $index) {
            $nameEn = "Category {$index}";
            $nameLt = "Kategorija {$index}";

            return Category::factory()->create([
                'parent_category_id' => null,
                'order' => $index,
                'category_name' => [
                    'en' => $nameEn,
                    'lt' => $nameLt,
                ],
                'slug' => [
                    'en' => Str::slug($nameEn),
                    'lt' => Str::slug($nameLt),
                ],
            ]);
        });

        $subcategories = collect();

        foreach ($mainCategories as $category) {
            $subcategories = $subcategories->merge(
                Category::factory()
                    ->count(3)
                    ->create([
                        'parent_category_id' => $category->id,
                    ])
            );
        }

        return [$mainCategories, $subcategories];
    }

    private function seedUsers(Collection $categories): array
    {
        Buyer::factory()->count(12)->create();
        $sellers = Seller::factory()->count(8)->create();

        $categorySellerMap = array_fill_keys($categories->pluck('id')->all(), []);

        foreach ($sellers as $seller) {
            $categoryIds = $categories
                ->random(min(5, $categories->count()))
                ->pluck('id')
                ->all();

            $seller->categories()->sync($categoryIds);

            foreach ($categoryIds as $categoryId) {
                $categorySellerMap[$categoryId][] = $seller->id;
            }
        }

        foreach ($categorySellerMap as $categoryId => $sellerIds) {
            if (empty($sellerIds)) {
                $fallbackSeller = $sellers->random();
                $fallbackSeller->categories()->syncWithoutDetaching([$categoryId]);
                $categorySellerMap[$categoryId][] = $fallbackSeller->id;
            }
        }

        return [$sellers, $categorySellerMap];
    }

    private function seedAttributes(Collection $categories): array
    {
        $attributeValues = [];
        $categoryAttributeMap = [];

        $attributes = Attribute::factory()
            ->count(6)
            ->state([
                'type' => 'select',
                'is_filterable' => true,
                'is_required' => false,
                'is_active' => true,
            ])
            ->create();

        foreach ($attributes as $attribute) {
            $values = AttributeValue::factory()
                ->count(4)
                ->state([
                    'attribute_id' => $attribute->id,
                    'is_active' => true,
                ])
                ->create();

            $attributeValues[$attribute->id] = $values;

            $categoriesForAttribute = $categories->random(
                min(4, max(1, $categories->count()))
            );

            $attribute->categories()->sync($categoriesForAttribute->pluck('id'));

            foreach ($categoriesForAttribute as $category) {
                $categoryAttributeMap[$category->id] ??= [];
                $categoryAttributeMap[$category->id][] = $attribute->id;
            }
        }

        return [$attributeValues, $categoryAttributeMap];
    }

    private function seedProducts(Collection $categories, Collection $countries, array $categorySellerMap): Collection
    {
        $products = collect();
        $placeholder = $this->createPlaceholderImage();

        foreach ($categories as $category) {
            $sellerIds = $categorySellerMap[$category->id] ?? [];
            if (empty($sellerIds)) {
                $sellerIds = collect($categorySellerMap)->flatten()->all();
            }

            if (empty($sellerIds)) {
                continue;
            }

            $products = $products->merge(
                Product::factory()
                    ->count(6)
                    ->state(fn () => [
                        'category_id' => $category->id,
                        'seller_id' => fake()->randomElement($sellerIds),
                        'country_of_origin' => $countries->random()->id,
                        'product_image' => $placeholder,
                        'product_additional_image' => null,
                        'is_active' => true,
                        'is_organic' => fake()->boolean(30),
                    ])
                    ->create()
            );
        }

        return $products;
    }

    private function attachAttributeValuesToProducts(Collection $products, array $attributeValues, array $categoryAttributeMap): void
    {
        foreach ($products as $product) {
            $attributeIds = $categoryAttributeMap[$product->category_id] ?? array_keys($attributeValues);

            foreach ($attributeIds as $attributeId) {
                if (!isset($attributeValues[$attributeId])) {
                    continue;
                }

                $value = $attributeValues[$attributeId]->random();

                $product->attributeValues()->attach($value->id, [
                    'attribute_id' => $attributeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function createPlaceholderImage(): string
    {
        $directory = 'public/products';
        $filename = 'placeholder.webp';
        $path = "{$directory}/{$filename}";

        if (!Storage::exists($path)) {
            Storage::makeDirectory($directory);
            $image = Image::canvas(600, 600, '#d1d5db')->encode('webp', 80);
            Storage::put($path, $image);
        }

        return $filename;
    }
}
