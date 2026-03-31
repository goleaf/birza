<?php

namespace Database\Seeders\test_information;

use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use RuntimeException;

class ProductSeeder extends Seeder
{
    private const PRODUCT_DIRECTORY = 'public/products';

    private const PRODUCTS_PER_SUBCATEGORY = 10;

    /**
     * @var array{en: array<int, string>, lt: array<int, string>}
     */
    private const PRODUCT_DESCRIPTIONS = [
        'en' => [
            'Fresh and crisp apples from sustainable orchards. Perfect for healthy snacking.',
            'Sweet and ripe bananas. Rich in potassium and great for smoothies.',
            'Juicy oranges bursting with vitamin C. Ideal for fresh juice.',
            'Fresh carrots from local farms. Great for salads and cooking.',
            'Tender broccoli rich in nutrients and easy to prepare.',
            'Fresh button mushrooms. Perfect for salads and cooking.',
            'Creamy yogurt. Great for breakfast and snacks.',
            'Aged cheddar cheese. Perfect for sandwiches.',
            'Sweet blackberries. Ideal for desserts and jams.',
            'Wild cranberries. Great for sauces and baking.',
        ],
        'lt' => [
            'Švieži ir traškūs obuoliai iš tvarių sodų. Puikiai tinka užkandžiams.',
            'Saldūs ir prinokę bananai. Turtingi kalio ir puikiai tinka kokteiliams.',
            'Sultingi apelsinai, kupini vitamino C. Idealūs šviežioms sultims.',
            'Šviežios morkos iš vietinių ūkių. Puikiai tinka salotoms ir maistui.',
            'Švelnus brokolis, turtingas maistinėmis medžiagomis ir universalus.',
            'Švieži pievagrybiai. Puikiai tinka salotoms ir maistui.',
            'Kreminis jogurtas. Puikiai tinka pusryčiams ir užkandžiams.',
            'Brandintas čederio sūris. Puikiai tinka sumuštiniams.',
            'Saldžios gervuogės. Puikiai tinka desertams ir džemams.',
            'Laukinės spanguolės. Puikiai tinka padažams ir kepiniams.',
        ],
    ];

    public function run(): void
    {
        $sellers = Seller::query()->select(['id'])->orderBy('id')->get();
        $countries = Country::query()
            ->where('region', 'Europe')
            ->select(['id'])
            ->orderBy('id')
            ->get();

        $mainCategories = Category::query()
            ->whereNull('parent_category_id')
            ->select(['id'])
            ->with([
                'subcategories' => fn ($query) => $query
                    ->select(['id', 'parent_category_id', 'category_name'])
                    ->orderBy('id'),
            ])
            ->get();

        /** @var Collection<int, Category> $subcategories */
        $subcategories = $mainCategories->flatMap->subcategories->values();

        if ($sellers->isEmpty() || $subcategories->isEmpty() || $countries->isEmpty()) {
            throw new RuntimeException('Sellers, categories, and European countries must exist before seeding products.');
        }

        Storage::makeDirectory(self::PRODUCT_DIRECTORY);

        foreach ($subcategories as $subcategory) {
            $this->seedProductsForSubcategory($subcategory, $sellers, $countries);
        }
    }

    /**
     * @param  Collection<int, Seller>  $sellers
     * @param  Collection<int, Country>  $countries
     */
    private function seedProductsForSubcategory(
        Category $subcategory,
        Collection $sellers,
        Collection $countries
    ): void {
        for ($slot = 0; $slot < self::PRODUCTS_PER_SUBCATEGORY; $slot++) {
            $productName = $this->productName($subcategory, $slot);
            $product = Product::query()->firstOrNew([
                'name' => $productName,
            ]);

            $seller = $sellers->get($slot % $sellers->count());
            $country = $countries->get(($subcategory->id + $slot) % $countries->count());
            $unit = Product::UNITS[$slot % count(Product::UNITS)];
            $stock = 20 + (($subcategory->id + $slot) % 80);
            $price = round(4.5 + (($subcategory->id % 11) * 0.7) + ($slot * 0.45), 2);
            $imagePath = $this->imagePath($subcategory, $slot);

            if (! Storage::exists($imagePath)) {
                $this->generateImage($imagePath, $this->colorFor($subcategory->id, $slot));
            }

            $product->fill([
                'name' => $productName,
                'category_id' => $subcategory->id,
                'seller_id' => $seller?->id,
                'price' => $price,
                'pack_type' => sprintf('Standard pack %02d', $slot + 1),
                'min_order_price' => round($price * max(1, (int) ceil(($slot + 1) / 2)), 2),
                'min_order_count' => max(1, (int) ceil($stock / 4)),
                'unit' => $unit,
                'is_organic' => $slot % 3 === 0,
                'country_of_origin' => $country?->id,
                'product_image' => basename($imagePath),
                'product_additional_image' => null,
                'is_active' => true,
                'package_weight' => round(0.5 + ($slot * 0.15), 3),
                'price_per_liter' => in_array($unit, ['l', 'kg'], true) ? $price : null,
                'stock' => $stock,
                'temperature_conditions_from' => 2,
                'temperature_conditions_to' => 6,
                'use_until' => now()->addDays(30 + $slot),
                'total_shelf_life' => 30 + $slot,
            ]);

            $product->setTranslations('description', $this->descriptionFor($subcategory, $slot));

            if (! $product->exists) {
                $product->created_at = now()->subDays($subcategory->id + $slot);
            }

            $product->save();
        }
    }

    private function productName(Category $subcategory, int $slot): string
    {
        return sprintf('Seed product %d-%02d', $subcategory->id, $slot + 1);
    }

    private function imagePath(Category $subcategory, int $slot): string
    {
        return sprintf('%s/seed-product-%d-%02d.webp', self::PRODUCT_DIRECTORY, $subcategory->id, $slot + 1);
    }

    private function colorFor(int $subcategoryId, int $slot): string
    {
        return '#'.substr(md5($subcategoryId.':'.$slot), 0, 6);
    }

    /**
     * @return array{en: string, lt: string}
     */
    private function descriptionFor(Category $subcategory, int $slot): array
    {
        $index = ($subcategory->id + $slot) % count(self::PRODUCT_DESCRIPTIONS['en']);

        return [
            'en' => self::PRODUCT_DESCRIPTIONS['en'][$index],
            'lt' => self::PRODUCT_DESCRIPTIONS['lt'][$index],
        ];
    }

    private function generateImage(string $path, string $color): void
    {
        $image = Image::canvas(500, 500, $color)->encode('webp', 80);

        Storage::put($path, $image);
    }
}
