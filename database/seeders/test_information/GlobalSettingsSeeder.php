<?php

namespace Database\Seeders\test_information;

use App\Models\GlobalSettings;
use Illuminate\Database\Seeder;

class GlobalSettingsSeeder extends Seeder
{
    private const DEFAULT_PORTAL_ADDITIONAL_PRICE = 0;

    public function run(): void
    {
        GlobalSettings::query()->updateOrCreate(
            ['id' => 1],
            ['portal_additional_price' => self::DEFAULT_PORTAL_ADDITIONAL_PRICE]
        );
    }
}
