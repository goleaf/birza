<?php

namespace Database\Seeders\test_information;

use App\Models\Category;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TestUsersSeeder extends Seeder
{
    private const TEST_PASSWORD = 'password123';

    private const TEST_EMAIL_DOMAIN = 'birza.lt';

    private const LITHUANIAN_STREETS = [
        'Gedimino pr.',
        'Pilies g.',
        'Vilniaus g.',
        'Kauno g.',
        'Laisvės al.',
        'Vytauto pr.',
        'Mindaugo g.',
        'Basanavičiaus g.',
        'Savanorių pr.',
        'Žirmūnų g.',
    ];

    private const LITHUANIAN_CITIES = [
        'Vilnius',
        'Kaunas',
        'Klaipėda',
        'Šiauliai',
        'Panevėžys',
    ];

    private const BUSINESS_CENTERS = [
        'Verslo centras "Europa"',
        'Verslo centras "Green Hall"',
        'Verslo centras "3 Burės"',
        'Verslo centras "Nova"',
        'Verslo centras "Quadrum"',
    ];

    /**
     * @var array<class-string<Buyer|Seller>, array<int, array{0: string, 1: string}>>
     */
    private const USERS = [
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
            ['Test buyer 10', 'buyer10'],
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
            ['Test seller 10', 'seller10'],
        ],
    ];

    public function run(): void
    {
        $subcategoryIds = Category::query()
            ->whereNotNull('parent_category_id')
            ->orderBy('id')
            ->pluck('id');

        foreach (self::USERS as $modelClass => $userDefinitions) {
            foreach ($userDefinitions as $index => [$name, $type]) {
                $user = $modelClass::query()->updateOrCreate(
                    ['email' => $this->emailFor($type)],
                    $this->userData($name, $type, $index, $modelClass === Buyer::class)
                );

                if ($user instanceof Seller) {
                    $user->categories()->sync($this->sellerCategoryIds($subcategoryIds, $index));
                }
            }
        }
    }

    private function emailFor(string $type): string
    {
        return $type.'@'.self::TEST_EMAIL_DOMAIN;
    }

    /**
     * @return array<string, mixed>
     */
    private function userData(string $name, string $type, int $index, bool $isBuyer): array
    {
        $data = [
            'name' => $name,
            'email' => $this->emailFor($type),
            'password' => self::TEST_PASSWORD,
            'vat_code' => sprintf('LT%09d', 100000000 + $index),
            'address' => $this->addressFor($index),
            'phone' => sprintf('+3706%07d', 1000000 + $index),
            'is_verified' => true,
            'is_active' => true,
            'company_code' => sprintf('%09d', 200000000 + $index),
            'company_name' => ucfirst($type).' test company',
        ];

        if ($isBuyer) {
            $data['bank_account'] = sprintf('LT1210000111010000%02d', $index);
            $data['credit_balance'] = 0;
        }

        return $data;
    }

    private function addressFor(int $index): string
    {
        $businessCenter = self::BUSINESS_CENTERS[$index % count(self::BUSINESS_CENTERS)];
        $street = self::LITHUANIAN_STREETS[$index % count(self::LITHUANIAN_STREETS)];
        $city = self::LITHUANIAN_CITIES[$index % count(self::LITHUANIAN_CITIES)];

        return sprintf(
            '%s, %s %d, LT-%05d, %s, Lithuania',
            $businessCenter,
            $street,
            10 + $index,
            10000 + $index,
            $city,
        );
    }

    /**
     * @param  Collection<int, int>  $subcategoryIds
     * @return array<int, int>
     */
    private function sellerCategoryIds(Collection $subcategoryIds, int $sellerIndex): array
    {
        if ($subcategoryIds->isEmpty()) {
            return [];
        }

        $selectionSize = min(4, $subcategoryIds->count());
        $categoryIds = [];

        for ($offset = 0; $offset < $selectionSize; $offset++) {
            $position = ($sellerIndex + $offset) % $subcategoryIds->count();
            $categoryIds[] = (int) $subcategoryIds->get($position);
        }

        return $categoryIds;
    }
}
