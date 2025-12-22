<?php

namespace Database\Seeders\test_information;

use App\Models\GlobalSettings;
use Illuminate\Database\Seeder;

class GlobalSettingsSeeder extends Seeder
{
    public function run()
    {
        $price = rand(1, 10);

        $settings = GlobalSettings::create([
            'portal_additional_price' => $price,
        ]);

    }
}
