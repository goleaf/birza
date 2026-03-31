<?php

namespace Database\Seeders;

use Database\Seeders\test_information\AttributesSeeder;
use Database\Seeders\test_information\CategorySeeder;
use Database\Seeders\test_information\CountriesSeeder;
use Database\Seeders\test_information\GlobalSettingsSeeder;
use Database\Seeders\test_information\ProductAttributeSeeder;
use Database\Seeders\test_information\ProductSeeder;
use Database\Seeders\test_information\TestUsersSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        Model::unguarded(function (): void {
            $this->call([
                CountriesSeeder::class,
                CategorySeeder::class,
                GlobalSettingsSeeder::class,
                TestUsersSeeder::class,
                ProductSeeder::class,
                AttributesSeeder::class,
                ProductAttributeSeeder::class,
                AdminSeeder::class,
            ]);
        });
    }
}
