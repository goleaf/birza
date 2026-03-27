<?php

namespace Database\Seeders\test_information;

use App\Models\Product;
use App\Models\Attribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->select(['id', 'category_id'])->get();
        $attributes = Attribute::query()
            ->select('id')
            ->with('values:id,attribute_id')
            ->get();

        if ($products->isEmpty() || $attributes->isEmpty()) {
            return;
        }

        $categoryIdsByAttribute = DB::table('category_attribute')
            ->select(['attribute_id', 'category_id'])
            ->get()
            ->groupBy('attribute_id')
            ->map(static fn ($rows) => $rows->pluck('category_id')->all())
            ->all();

        $valueIdsByAttribute = $attributes
            ->mapWithKeys(static fn (Attribute $attribute) => [
                $attribute->id => $attribute->values->pluck('id')->all(),
            ])
            ->all();

        $existingPairs = DB::table('attribute_product')
            ->select(['product_id', 'attribute_id'])
            ->get()
            ->mapWithKeys(static fn ($row) => [
                $row->product_id . ':' . $row->attribute_id => true,
            ])
            ->all();

        $rows = [];

        foreach ($products as $product) {
            foreach ($attributes as $attribute) {
                $pairKey = $product->id . ':' . $attribute->id;
                if (isset($existingPairs[$pairKey])) {
                    continue;
                }

                $allowedCategories = $categoryIdsByAttribute[$attribute->id] ?? [];
                if (!in_array($product->category_id, $allowedCategories)) {
                    continue;
                }

                $valueIds = $valueIdsByAttribute[$attribute->id] ?? [];
                if ($valueIds === []) {
                    continue;
                }

                $rows[] = [
                    'attribute_id' => $attribute->id,
                    'product_id' => $product->id,
                    'selected_value_id' => $valueIds[array_rand($valueIds)],
                ];
            }
        }

        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('attribute_product')->insertOrIgnore($chunk);
        }
    }
}
