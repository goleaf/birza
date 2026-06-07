<?php

namespace Database\Seeders;

use Database\Seeders\test_information\CategorySeeder;
use Database\Seeders\test_information\CountriesSeeder;
use Database\Seeders\test_information\GlobalSettingsSeeder;
use Illuminate\Database\Seeder;

class MinimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            CountriesSeeder::class,
            CategorySeeder::class,
            GlobalSettingsSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
