<?php

namespace Database\Seeders\test_information;

use App\Models\Category;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    private const TEST_PASSWORD = 'password123';
    private const TEST_EMAIL_DOMAIN = 'birza.lt';

    public function run(): void
    {
        $faker = Faker::create();

        $lithuanianStreets = [
            'Gedimino pr.',
            'Pilies g.',
            'Vilniaus g.',
            'Kauno g.',
            'Laisvės al.',
            'Vytauto pr.',
            'Mindaugo g.',
            'Basanavičiaus g.',
            'Savanorių pr.',
            'Žirmūnų g.'
        ];

        $lithuanianCities = [
            'Vilnius',
            'Kaunas',
            'Klaipėda',
            'Šiauliai',
            'Panevėžys'
        ];

        $businessCenters = [
            'Verslo centras "Europa"',
            'Verslo centras "Green Hall"',
            'Verslo centras "3 Burės"',
            'Verslo centras "Nova"',
            'Verslo centras "Quadrum"'
        ];

        $users = [
            Buyer::class => [
                ['Test buyer 1', 'buyer1'],
                ['Test buyer 2', 'buyer2'],
                ['Test buyer 3', 'buyer3'],
                ['Test buyer 4', 'buyer4'],
                ['Test buyer 5', 'buyer5'],
                ['Test buyer 6', 'buyer6'],
                ['Test buyer 7', 'buyer7'],
                ['Test buyer 8', 'buyer8'],
                ['Test buyer 9', 'buyer9'],
                ['Test buyer 10', 'buyer10']
            ],
            Seller::class => [
                ['Test seller 1', 'seller1'],
                ['Test seller 2', 'seller2'],
                ['Test seller 3', 'seller3'],
                ['Test seller 4', 'seller4'],
                ['Test seller 5', 'seller5'],
                ['Test seller 6', 'seller6'],
                ['Test seller 7', 'seller7'],
                ['Test seller 8', 'seller8'],
                ['Test seller 9', 'seller9'],
                ['Test seller 10', 'seller10']
            ]
        ];

        foreach ($users as $model => $userList) {
            foreach ($userList as [$name, $type]) {
                $address = $faker->randomElement($businessCenters) . ', ' .
                          $faker->randomElement($lithuanianStreets) . ' ' .
                          $faker->buildingNumber . ', ' .
                          ($faker->boolean(70) ? $faker->numerify('LT-#####') . ', ' : '') .
                          $faker->randomElement($lithuanianCities) . ', Lithuania';

                $userData = [
                    'name' => $name,
                    'email' => $type . '@' . self::TEST_EMAIL_DOMAIN,
                    'password' => Hash::make(self::TEST_PASSWORD),
                    'vat_code' => 'LT' . $faker->numerify('#########'),
                    'address' => $address,
                    'phone' => '+370' . $faker->numerify('########'),
                    'is_verified' => true,
                    'company_code' => 'LT' . $faker->numerify('#########'),
                    'company_name' => ucfirst($type) . ' test company',
                ];

                $user = $model::create($userData);

                if ($model === Seller::class) {
                    $subcategories = Category::whereNotNull('parent_category_id')
                                           ->inRandomOrder()
                                           ->take(4)
                                           ->pluck('id');

                    $user->categories()->attach($subcategories);
                }
            }
        }
    }
}
