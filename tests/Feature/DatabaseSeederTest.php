<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    private string $sqliteDatabasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqliteDatabasePath = database_path('database-seeder-test.sqlite');

        File::delete($this->sqliteDatabasePath);
        File::put($this->sqliteDatabasePath, '');

        DB::purge('sqlite');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->sqliteDatabasePath,
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        File::delete($this->sqliteDatabasePath);

        parent::tearDown();
    }

    public function test_database_can_be_fresh_migrated_and_seeded(): void
    {
        $this->artisan('migrate:fresh', [
            '--seed' => true,
            '--database' => 'sqlite',
            '--no-interaction' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users_admins', [
            'email' => 'admin@admin.com',
        ]);
    }
}
