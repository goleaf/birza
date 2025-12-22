<?php

namespace Database\Seeders;

use Database\Seeders\test_information\CategorySeeder;
use Database\Seeders\test_information\CountriesSeeder;
use Database\Seeders\test_information\ProductSeeder;
use Database\Seeders\test_information\TestUsersSeeder;
use Database\Seeders\test_information\GlobalSettingsSeeder;
use Database\Seeders\test_information\AttributesSeeder;
use Database\Seeders\test_information\ProductAttributeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        dump("=== Starting Database Seeder ===");
        dump("Initializing database seeding process...");
        dump("This will populate the database with test data");

        $seeders = [
            CountriesSeeder::class,
            CategorySeeder::class,
            GlobalSettingsSeeder::class,
            TestUsersSeeder::class,
            ProductSeeder::class,
            AttributesSeeder::class,
            ProductAttributeSeeder::class
        ];

        $totalSeeders = count($seeders);
        dump("\nSeeder Information:");
        dump("- Total seeders to execute: {$totalSeeders}");
        dump("- Estimated time: ~2-3 minutes");
        dump("- Database will be modified");

        foreach($seeders as $index => $seeder) {
            $current = $index + 1;
            $seederName = class_basename($seeder);

            dump("\n[{$current}/{$totalSeeders}] Executing: {$seederName}");
            dump("- Purpose: Seeding " . strtolower(preg_replace('/Seeder$/', '', $seederName)) . " data");
            dump("- Status: Starting...");

            $startTime = microtime(true);
            $memoryBefore = memory_get_usage();

            $this->call($seeder);

            $endTime = microtime(true);
            $memoryAfter = memory_get_usage();
            $memoryUsed = round(($memoryAfter - $memoryBefore) / 1024 / 1024, 2);
            $duration = round($endTime - $startTime, 2);

            dump("Seeder Completion Stats:");
            dump("- Time taken: {$duration} seconds");
            dump("- Memory used: {$memoryUsed} MB");
            dump("- Status: Successfully completed");
        }

        dump("\n=== Database Seeding Complete ===");
        dump("All {$totalSeeders} seeders executed successfully");
        dump("Database is now populated with test data");
        dump("Ready for work, man, good luck, make with love for by prus!!");
    }
}
