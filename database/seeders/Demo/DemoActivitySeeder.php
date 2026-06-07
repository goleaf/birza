<?php

namespace Database\Seeders\Demo;

use App\Models\Activity;
use App\Models\AdminAction;
use Database\Seeders\AuditLogSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoActivitySeeder extends Seeder
{
    private const TARGET_ACTIVITY_ROWS = 8;

    private const TARGET_ADMIN_ACTION_ROWS = 8;

    public function run(): void
    {
        $this->topUp(Activity::class, 'activities', self::TARGET_ACTIVITY_ROWS);
        $this->topUp(AdminAction::class, 'admin_actions', self::TARGET_ADMIN_ACTION_ROWS);

        if (Schema::hasTable('audit_logs')) {
            $this->call(AuditLogSeeder::class);
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function topUp(string $modelClass, string $table, int $targetCount): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existingCount = $modelClass::query()->count();

        for ($index = $existingCount; $index < $targetCount; $index++) {
            $modelClass::factory()->create([
                'created_at' => now()->subHours($index + 1),
                'updated_at' => now()->subHours($index + 1),
            ]);
        }
    }
}
