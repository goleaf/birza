<?php

namespace Database\Seeders\test_information;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CountriesSeeder extends Seeder
{
    private const COUNTRIES_JSON_PATH = 'database/seeders/countries_json/countries_list.json';

    private const EN_TRANSLATIONS_PATH = 'database/seeders/countries_json/translations/countries_en.json';

    private const LT_TRANSLATIONS_PATH = 'database/seeders/countries_json/translations/countries_lt.json';

    private const TRANSLATION_NAME_KEY = 'region';

    public function run(): void
    {
        $countriesData = $this->loadJsonFile(base_path(self::COUNTRIES_JSON_PATH));
        $ltTranslations = $this->loadTranslationMap(
            base_path(self::LT_TRANSLATIONS_PATH)
        );
        $enTranslations = $this->loadTranslationMap(
            base_path(self::EN_TRANSLATIONS_PATH)
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
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadJsonFile(string $path): array
    {
        return json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function loadTranslationMap(string $path): array
    {
        $data = $this->loadJsonFile($path);
        $translations = [];

        foreach ($data as $entry) {
            if (empty($entry['alpha2']) || ! array_key_exists(self::TRANSLATION_NAME_KEY, $entry)) {
                continue;
            }

            $translations[Str::lower($entry['alpha2'])] = $entry[self::TRANSLATION_NAME_KEY];
        }

        return $translations;
    }
}
