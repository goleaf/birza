<?php

namespace Database\Seeders\test_information;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Faker\Factory;

class AttributesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        $products = Product::all();

        if ($products->isEmpty()) {
            return;
        }

        $attributeData = [
            [
                'en' => 'Nutritional Value',
                'lt' => 'Maistinė vertė',
                'values' => [
                    ['en' => 'High Protein', 'lt' => 'Didelis baltymiškumas'],
                    ['en' => 'Low Fat', 'lt' => 'Mažai riebalų'],
                    ['en' => 'Rich in Vitamins', 'lt' => 'Turtingas vitaminais']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Dietary Type',
                'lt' => 'Mitybos tipas',
                'values' => [
                    ['en' => 'Vegan', 'lt' => 'Veganiška'],
                    ['en' => 'Vegetarian', 'lt' => 'Vegetariška'],
                    ['en' => 'Gluten-Free', 'lt' => 'Be gliuteno'],
                    ['en' => 'Organic', 'lt' => 'Ekologiška']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Origin',
                'lt' => 'Kilmė',
                'values' => [
                    ['en' => 'Local', 'lt' => 'Vietinė'],
                    ['en' => 'Imported', 'lt' => 'Importinė'],
                    ['en' => 'Artisan', 'lt' => 'Amatininkų']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Preservation Method',
                'lt' => 'Išsaugojimo metodas',
                'values' => [
                    ['en' => 'Fresh', 'lt' => 'Šviežia'],
                    ['en' => 'Frozen', 'lt' => 'Užšaldyta'],
                    ['en' => 'Canned', 'lt' => 'Konservuota']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Spiciness',
                'lt' => 'Aštrumas',
                'values' => [
                    ['en' => 'Mild', 'lt' => 'Švelnus'],
                    ['en' => 'Medium', 'lt' => 'Vidutinis'],
                    ['en' => 'Spicy', 'lt' => 'Aštrus']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Packaging',
                'lt' => 'Pakavimas',
                'values' => [
                    ['en' => 'Plastic', 'lt' => 'Plastikas'],
                    ['en' => 'Glass', 'lt' => 'Stiklas'],
                    ['en' => 'Cardboard', 'lt' => 'Kartonas'],
                    ['en' => 'Eco-Friendly', 'lt' => 'Aplinkai draugiškas']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Temperature Range',
                'lt' => 'Temperatūros diapazonas',
                'values' => [
                    ['en' => 'Room Temperature', 'lt' => 'Kambario temperatūra'],
                    ['en' => 'Refrigerated', 'lt' => 'Šaldytuvas'],
                    ['en' => 'Frozen', 'lt' => 'Užšaldyta']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Allergen Information',
                'lt' => 'Alergenų informacija',
                'values' => [
                    ['en' => 'Nuts', 'lt' => 'Riešutai'],
                    ['en' => 'Dairy', 'lt' => 'Pieno produktai'],
                    ['en' => 'Eggs', 'lt' => 'Kiaušiniai'],
                    ['en' => 'Soy', 'lt' => 'Soja'],
                    ['en' => 'Wheat', 'lt' => 'Kviečiai']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Weight',
                'lt' => 'Svoris',
                'values' => [
                    ['en' => 'Light', 'lt' => 'Lengvas'],
                    ['en' => 'Medium', 'lt' => 'Vidutinis'],
                    ['en' => 'Heavy', 'lt' => 'Sunkus']
                ],
                'is_filterable' => true,
                'is_required' => false
            ],
            [
                'en' => 'Cooking Method',
                'lt' => 'Gaminimo metodas',
                'values' => [
                    ['en' => 'Boiled', 'lt' => 'Virtas'],
                    ['en' => 'Grilled', 'lt' => 'Keptas ant grotelių'],
                    ['en' => 'Baked', 'lt' => 'Keptas orkaitėje'],
                    ['en' => 'Raw', 'lt' => 'Žalias']
                ],
                'is_filterable' => true,
                'is_required' => false
            ]
        ];

        foreach ($attributeData as $attributeInfo) {
            $attribute = Attribute::create([
                'name' => [
                    'en' => $attributeInfo['en'],
                    'lt' => $attributeInfo['lt']
                ],
                'type' => 'select',
                'is_active' => true,
                'is_filterable' => $attributeInfo['is_filterable'],
                'is_required' => $attributeInfo['is_required']
            ]);

            foreach ($attributeInfo['values'] as $valueInfo) {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => [
                        'en' => $valueInfo['en'],
                        'lt' => $valueInfo['lt']
                    ],
                    'is_active' => true
                ]);
            }
        }

        $allAttributeValues = AttributeValue::all();

        foreach ($products as $product) {
            foreach ($allAttributeValues as $attributeValue) {
                $product->attributeValues()->attach($attributeValue->id, [
                    'attribute_id' => $attributeValue->attribute_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

    }
}
