<?php

namespace Database\Seeders\test_information;

use App\Models\Attribute;
use App\Models\AttributeProduct;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()
            ->select(['id', 'category_id'])
            ->orderBy('id')
            ->get();

        $attributes = Attribute::query()
            ->select(['id'])
            ->with([
                'categories:id',
                'values' => fn ($query) => $query
                    ->select(['id', 'attribute_id'])
                    ->orderBy('id'),
            ])
            ->get();

        if ($products->isEmpty() || $attributes->isEmpty()) {
            return;
        }

        $existingPairs = AttributeProduct::query()
            ->select(['product_id', 'attribute_id'])
            ->get()
            ->mapWithKeys(static fn (AttributeProduct $attributeProduct): array => [
                $attributeProduct->product_id.':'.$attributeProduct->attribute_id => true,
            ])
            ->all();

        $rows = [];

        foreach ($products as $product) {
            foreach ($attributes as $attribute) {
                $pairKey = $product->id.':'.$attribute->id;

                if (isset($existingPairs[$pairKey])) {
                    continue;
                }

                if (! $attribute->categories->contains('id', $product->category_id)) {
                    continue;
                }

                $selectedValueId = $this->selectedValueId($attribute->values, $product->id, $attribute->id);

                if ($selectedValueId === null) {
                    continue;
                }

                $rows[] = [
                    'attribute_id' => $attribute->id,
                    'product_id' => $product->id,
                    'selected_value_id' => $selectedValueId,
                ];

                $existingPairs[$pairKey] = true;
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            AttributeProduct::query()->insert($chunk);
        }
    }

    /**
     * @param  Collection<int, AttributeValue>  $values
     */
    private function selectedValueId(Collection $values, int $productId, int $attributeId): ?int
    {
        if ($values->isEmpty()) {
            return null;
        }

        $index = ($productId + $attributeId) % $values->count();

        return $values->values()->get($index)?->id;
    }
}
