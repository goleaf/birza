<?php

namespace Database\Seeders\test_information;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countriesJsonPath = base_path('database/seeders/countries_json/countries_list.json');
        $countriesData = json_decode(File::get($countriesJsonPath), true);
        $ltTranslations = $this->loadTranslationMap(
            base_path('database/seeders/countries_json/translations/countries_lt.json')
        );
        $enTranslations = $this->loadTranslationMap(
            base_path('database/seeders/countries_json/translations/countries_en.json')
        );

        $rows = [];

        foreach ($countriesData as $country) {
            if (empty($country['region']) || empty($country['alpha2'])) {
                continue;
            }

            $alpha2 = Str::lower($country['alpha2']);

            $rows[] = [
                'alpha2' => $alpha2,
                'region' => $country['region'],
                'is_active' => true,
                'country_name' => json_encode([
                    'lt' => $ltTranslations[$alpha2] ?? $country['name'] ?? $alpha2,
                    'en' => $enTranslations[$alpha2] ?? $country['name'] ?? $alpha2,
                ], JSON_UNESCAPED_UNICODE),
            ];
        }

        if ($rows === []) {
            return;
        }

        Country::query()->upsert(
            $rows,
            ['alpha2'],
            ['region', 'is_active', 'country_name']
        );
    }

    private function loadTranslationMap(string $path): array
    {
        $data = json_decode(File::get($path), true);
        $translations = [];

        foreach ($data as $entry) {
            if (empty($entry['alpha2']) || ! array_key_exists('country_name', $entry)) {
                continue;
            }

            $translations[Str::lower($entry['alpha2'])] = $entry['country_name'];
        }

        return $translations;
    }
}
