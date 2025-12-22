<?php

namespace Database\Seeders\test_information;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countriesJsonPath = base_path('database/seeders/test_information/countries_json/countries_list.json');
        $countriesData = json_decode(File::get($countriesJsonPath), true);

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($countriesData as $country) {
            if (!empty($country['region'])) {
                $translations = [
                    'lt' => json_decode(File::get(base_path('database/seeders/test_information/countries_json/translations/countries_lt.json')), true),
                    'en' => json_decode(File::get(base_path('database/seeders/test_information/countries_json/translations/countries_en.json')), true),
                ];

                $translatedNames = [];
                foreach ($translations as $locale => $countryTranslations) {
                    foreach ($countryTranslations as $alpha2 => $translationData) {
                        if (Str::lower($translationData['alpha2']) === Str::lower($country['alpha2'])) {
                            $translatedNames[$locale] = $translationData['country_name'];
                        }
                    }
                }

                $countryModel = Country::create([
                    'alpha2' => Str::lower($country['alpha2']),
                    'region' => $country['region'],
                    'country_name' => $translatedNames
                ]);

                $processedCount++;
            } else {
                $skippedCount++;
            }
        }
    }
}
