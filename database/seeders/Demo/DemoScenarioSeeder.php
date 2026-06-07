<?php

namespace Database\Seeders\Demo;

use Database\Seeders\test_information\AttributesSeeder;
use Database\Seeders\test_information\ProductAttributeSeeder;
use Database\Seeders\test_information\ProductSeeder;
use Database\Seeders\test_information\TestUsersSeeder;
use Illuminate\Database\Seeder;

class DemoScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TestUsersSeeder::class,
            ProductSeeder::class,
            AttributesSeeder::class,
            DemoUserSeeder::class,
            DemoCatalogSeeder::class,
            AttributesSeeder::class,
            ProductAttributeSeeder::class,
            DemoProductImageSeeder::class,
            DemoPromotionSeeder::class,
            DemoProductBundleSeeder::class,
            DemoCartSeeder::class,
            DemoWishlistSeeder::class,
            DemoOrderSeeder::class,
            DemoMessagingSeeder::class,
            DemoStockAlertSeeder::class,
            DemoProductQuestionSeeder::class,
            DemoReviewSeeder::class,
            DemoProductReportSeeder::class,
            DemoNotificationSeeder::class,
            DemoCreditSeeder::class,
            DemoActivitySeeder::class,
        ]);
    }
}
