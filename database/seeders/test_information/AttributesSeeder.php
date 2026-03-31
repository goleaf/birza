<?php

namespace Database\Seeders\test_information;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AttributesSeeder extends Seeder
{
    private const ATTRIBUTE_DEFINITIONS = [
        [
            'name' => ['en' => 'Nutritional Value', 'lt' => 'Maistinė vertė'],
            'values' => [
                ['en' => 'High Protein', 'lt' => 'Didelis baltymiškumas'],
                ['en' => 'Low Fat', 'lt' => 'Mažai riebalų'],
                ['en' => 'Rich in Vitamins', 'lt' => 'Turtingas vitaminais'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Dietary Type', 'lt' => 'Mitybos tipas'],
            'values' => [
                ['en' => 'Vegan', 'lt' => 'Veganiška'],
                ['en' => 'Vegetarian', 'lt' => 'Vegetariška'],
                ['en' => 'Gluten-Free', 'lt' => 'Be gliuteno'],
                ['en' => 'Organic', 'lt' => 'Ekologiška'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Origin', 'lt' => 'Kilmė'],
            'values' => [
                ['en' => 'Local', 'lt' => 'Vietinė'],
                ['en' => 'Imported', 'lt' => 'Importinė'],
                ['en' => 'Artisan', 'lt' => 'Amatininkų'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Preservation Method', 'lt' => 'Išsaugojimo metodas'],
            'values' => [
                ['en' => 'Fresh', 'lt' => 'Šviežia'],
                ['en' => 'Frozen', 'lt' => 'Užšaldyta'],
                ['en' => 'Canned', 'lt' => 'Konservuota'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Spiciness', 'lt' => 'Aštrumas'],
            'values' => [
                ['en' => 'Mild', 'lt' => 'Švelnus'],
                ['en' => 'Medium', 'lt' => 'Vidutinis'],
                ['en' => 'Spicy', 'lt' => 'Aštrus'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Packaging', 'lt' => 'Pakavimas'],
            'values' => [
                ['en' => 'Plastic', 'lt' => 'Plastikas'],
                ['en' => 'Glass', 'lt' => 'Stiklas'],
                ['en' => 'Cardboard', 'lt' => 'Kartonas'],
                ['en' => 'Eco-Friendly', 'lt' => 'Aplinkai draugiškas'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Temperature Range', 'lt' => 'Temperatūros diapazonas'],
            'values' => [
                ['en' => 'Room Temperature', 'lt' => 'Kambario temperatūra'],
                ['en' => 'Refrigerated', 'lt' => 'Šaldytuvas'],
                ['en' => 'Frozen', 'lt' => 'Užšaldyta'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Allergen Information', 'lt' => 'Alergenų informacija'],
            'values' => [
                ['en' => 'Nuts', 'lt' => 'Riešutai'],
                ['en' => 'Dairy', 'lt' => 'Pieno produktai'],
                ['en' => 'Eggs', 'lt' => 'Kiaušiniai'],
                ['en' => 'Soy', 'lt' => 'Soja'],
                ['en' => 'Wheat', 'lt' => 'Kviečiai'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Weight', 'lt' => 'Svoris'],
            'values' => [
                ['en' => 'Light', 'lt' => 'Lengvas'],
                ['en' => 'Medium', 'lt' => 'Vidutinis'],
                ['en' => 'Heavy', 'lt' => 'Sunkus'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
        [
            'name' => ['en' => 'Cooking Method', 'lt' => 'Gaminimo metodas'],
            'values' => [
                ['en' => 'Boiled', 'lt' => 'Virtas'],
                ['en' => 'Grilled', 'lt' => 'Keptas ant grotelių'],
                ['en' => 'Baked', 'lt' => 'Keptas orkaitėje'],
                ['en' => 'Raw', 'lt' => 'Žalias'],
            ],
            'is_filterable' => true,
            'is_required' => false,
        ],
    ];

    public function run(): void
    {
        if (! Product::query()->exists()) {
            return;
        }

        $attributeValuePivotData = $this->seedAttributesAndValues();

        if ($attributeValuePivotData === []) {
            return;
        }

        $this->seedProductAttributeValues($attributeValuePivotData);
    }

    /**
     * @return array<int, array{attribute_id: int}>
     */
    private function seedAttributesAndValues(): array
    {
        /** @var Collection<string, Attribute> $attributesBySignature */
        $attributesBySignature = Attribute::query()
            ->with('values')
            ->get()
            ->mapWithKeys(function (Attribute $attribute): array {
                return [
                    $this->attributeSignature(
                        $attribute->getTranslations('name'),
                        (bool) $attribute->is_filterable,
                        (bool) $attribute->is_required,
                    ) => $attribute,
                ];
            });

        $attributeValuePivotData = [];

        foreach (self::ATTRIBUTE_DEFINITIONS as $attributeDefinition) {
            $attribute = $this->resolveAttribute($attributesBySignature, $attributeDefinition);

            /** @var Collection<string, AttributeValue> $valuesBySignature */
            $valuesBySignature = $attribute->values->keyBy(
                fn (AttributeValue $attributeValue): string => $this->valueSignature($attributeValue->getTranslations('value'))
            );

            foreach ($attributeDefinition['values'] as $valueDefinition) {
                $attributeValue = $this->resolveAttributeValue($attribute, $valuesBySignature, $valueDefinition);

                $attributeValuePivotData[$attributeValue->getKey()] = [
                    'attribute_id' => $attribute->getKey(),
                ];
            }
        }

        return $attributeValuePivotData;
    }

    /**
     * @param  Collection<string, Attribute>  $attributesBySignature
     * @param  array{name: array{en: string, lt: string}, values: array<int, array{en: string, lt: string}>, is_filterable: bool, is_required: bool}  $attributeDefinition
     */
    private function resolveAttribute(Collection $attributesBySignature, array $attributeDefinition): Attribute
    {
        $signature = $this->attributeSignature(
            $attributeDefinition['name'],
            $attributeDefinition['is_filterable'],
            $attributeDefinition['is_required'],
        );

        $attribute = $attributesBySignature->get($signature);

        if ($attribute !== null) {
            return $attribute;
        }

        $attribute = Attribute::query()->create([
            'name' => $attributeDefinition['name'],
            'type' => 'select',
            'is_active' => true,
            'is_filterable' => $attributeDefinition['is_filterable'],
            'is_required' => $attributeDefinition['is_required'],
        ]);

        $attribute->setRelation('values', collect());
        $attributesBySignature->put($signature, $attribute);

        return $attribute;
    }

    /**
     * @param  Collection<string, AttributeValue>  $valuesBySignature
     * @param  array{en: string, lt: string}  $valueDefinition
     */
    private function resolveAttributeValue(
        Attribute $attribute,
        Collection $valuesBySignature,
        array $valueDefinition
    ): AttributeValue {
        $signature = $this->valueSignature($valueDefinition);

        $attributeValue = $valuesBySignature->get($signature);

        if ($attributeValue !== null) {
            return $attributeValue;
        }

        $attributeValue = $attribute->values()->create([
            'value' => $valueDefinition,
            'is_active' => true,
        ]);

        $attribute->values->push($attributeValue);
        $valuesBySignature->put($signature, $attributeValue);

        return $attributeValue;
    }

    /**
     * @param  array<int, array{attribute_id: int}>  $attributeValuePivotData
     */
    private function seedProductAttributeValues(array $attributeValuePivotData): void
    {
        Product::query()
            ->select(['id'])
            ->chunkById(100, function (Collection $products) use ($attributeValuePivotData): void {
                $productIds = $products->modelKeys();

                /** @var Collection<int, array<int, bool>> $existingValueIdsByProduct */
                $existingValueIdsByProduct = ProductAttributeValue::query()
                    ->select(['product_id', 'attribute_value_id'])
                    ->whereIn('product_id', $productIds)
                    ->get()
                    ->groupBy('product_id')
                    ->map(static function (Collection $rows): array {
                        return $rows->pluck('attribute_value_id')->mapWithKeys(
                            static fn (int $attributeValueId): array => [$attributeValueId => true]
                        )->all();
                    });

                $timestamp = now();
                $rows = [];

                foreach ($products as $product) {
                    $existingValueIds = $existingValueIdsByProduct->get($product->getKey(), []);

                    foreach ($attributeValuePivotData as $attributeValueId => $pivotData) {
                        if (isset($existingValueIds[$attributeValueId])) {
                            continue;
                        }

                        $rows[] = [
                            'product_id' => $product->getKey(),
                            'attribute_id' => $pivotData['attribute_id'],
                            'attribute_value_id' => $attributeValueId,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if ($rows !== []) {
                    ProductAttributeValue::query()->insert($rows);
                }
            });
    }

    /**
     * @param  array{en?: string, lt?: string}  $translations
     */
    private function attributeSignature(array $translations, bool $isFilterable, bool $isRequired): string
    {
        return implode('|', [
            'select',
            $translations['en'] ?? '',
            $translations['lt'] ?? '',
            $isFilterable ? '1' : '0',
            $isRequired ? '1' : '0',
        ]);
    }

    /**
     * @param  array{en?: string, lt?: string}  $translations
     */
    private function valueSignature(array $translations): string
    {
        return implode('|', [
            $translations['en'] ?? '',
            $translations['lt'] ?? '',
        ]);
    }
}
