<?php

namespace Database\Seeders;

use Database\Seeders\Demo\DemoScenarioSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        Model::unguarded(function (): void {
            $this->call(MinimalSeeder::class);

            if (app()->environment('production')) {
                return;
            }

            $this->call(DemoScenarioSeeder::class);
            $this->call(AuditLogSeeder::class);
        });
    }
}
