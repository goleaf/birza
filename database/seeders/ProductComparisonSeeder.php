<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductComparisonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seller = Seller::query()->firstOrNew(['email' => 'comparison-seller@example.test']);
        $seller->forceFill([
            'name' => 'Comparison Seller',
            'password' => Hash::make('password'),
            'company_name' => 'Comparison Fresh Foods',
            'company_code' => 'CMP000001',
            'vat_code' => 'LTCMP000001',
            'address' => 'Comparison Street 1',
            'phone' => '+37060000001',
            'bank_account' => 'LT000000000000000000',
            'is_verified' => true,
            'is_active' => true,
        ])->save();

        $country = Country::query()->updateOrCreate(
            ['alpha2' => 'LT'],
            [
                'region' => 'Europe',
                'is_active' => true,
                'country_name' => ['en' => 'Lithuania', 'lt' => 'Lietuva'],
                'description' => [
                    'en' => 'Lithuanian comparison demo origin.',
                    'lt' => 'Lietuvos palyginimo demo kilmes salis.',
                ],
            ],
        );

        $parentCategory = Category::query()
            ->where('slug->en', 'comparison-foods')
            ->first();

        if (! $parentCategory) {
            $parentCategory = new Category;
            $parentCategory->forceFill([
                'category_name' => ['en' => 'Comparison Foods', 'lt' => 'Palyginimo maistas'],
                'slug' => ['en' => 'comparison-foods', 'lt' => 'palyginimo-maistas'],
                'order' => 900,
                'is_active' => true,
            ])->save();
        }

        $category = Category::query()
            ->where('slug->en', 'comparison-dairy')
            ->first();

        if (! $category) {
            $category = new Category;
            $category->forceFill([
                'parent_category_id' => $parentCategory->id,
                'category_name' => ['en' => 'Comparison Dairy', 'lt' => 'Palyginimo pieno produktai'],
                'slug' => ['en' => 'comparison-dairy', 'lt' => 'palyginimo-pieno-produktai'],
                'order' => 901,
                'is_active' => true,
            ])->save();
        }

        collect([
            [
                'name' => 'Comparison Greek Yogurt',
                'price' => 4.90,
                'stock' => 42,
                'product_image' => 'comparison-greek-yogurt.webp',
                'is_active' => true,
            ],
            [
                'name' => 'Comparison Kefir Bottle',
                'price' => 2.80,
                'stock' => 60,
                'product_image' => 'comparison-kefir-bottle.webp',
                'is_active' => true,
            ],
            [
                'name' => 'Comparison Cottage Cheese',
                'price' => 3.45,
                'stock' => 0,
                'product_image' => 'comparison-cottage-cheese.webp',
                'is_active' => true,
            ],
            [
                'name' => 'Comparison No Image Milk',
                'price' => 1.95,
                'stock' => 35,
                'product_image' => '',
                'is_active' => true,
            ],
            [
                'name' => 'Comparison Inactive Cheese',
                'price' => 5.20,
                'stock' => 25,
                'product_image' => 'comparison-inactive-cheese.webp',
                'is_active' => false,
            ],
        ])->each(function (array $attributes) use ($seller, $country, $category): void {
            $product = Product::factory()
                ->make(array_merge([
                    'category_id' => $category->id,
                    'seller_id' => $seller->id,
                    'country_of_origin' => $country->id,
                    'unit' => 'kg',
                    'min_order_count' => 1,
                    'min_order_price' => $attributes['price'],
                    'description' => [
                        'en' => 'Comparison-ready demo product for checking buyer decision fields.',
                        'lt' => 'Palyginimui tinkama demo preke pirkejo sprendimo laukams tikrinti.',
                    ],
                    'product_additional_image' => null,
                    'image_library' => [],
                    'is_organic' => true,
                ], $attributes));

            $persistedProduct = Product::query()->firstOrNew(['name' => $product->name]);
            $persistedProduct->forceFill($product->getAttributes())->save();
        });
    }
}
