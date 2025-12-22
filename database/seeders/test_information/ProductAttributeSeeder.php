<?php

namespace Database\Seeders\test_information;

use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with('category')->get();
        $attributes = Attribute::with(['values', 'categories'])->get();

        foreach ($products as $product) {
            foreach ($attributes as $attribute) {
                if (!$attribute->categories->contains($product->category_id)) {
                    continue;
                }

                $validValues = $attribute->values;
                if ($validValues->isEmpty()) {
                    continue;
                }

                $existingAttribute = $product->attributes()
                    ->where('attribute_id', $attribute->id)
                    ->first();

                if ($existingAttribute) {
                    continue;
                }

                $randomValue = $validValues->random();

                if ($randomValue->attribute_id !== $attribute->id) {
                    continue;
                }

                $product->attributes()->attach($attribute->id, [
                    'selected_value_id' => $randomValue->id
                ]);
            }
        }
    }
}
