<?php

namespace Database\Seeders\test_information;

use App\Models\GlobalSettings;
use Illuminate\Database\Seeder;

class GlobalSettingsSeeder extends Seeder
{
    public function run()
    {
        GlobalSettings::query()->updateOrCreate(
            ['id' => 1],
            ['portal_additional_price' => random_int(1, 10)]
        );
    }
}
