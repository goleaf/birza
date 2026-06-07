<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    private string $sqliteDatabasePath;

    /**
     * @var array{default: string, sqlite_database: mixed}
     */
    private array $previousDatabaseConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousDatabaseConfig = [
            'default' => (string) config('database.default'),
            'sqlite_database' => config('database.connections.sqlite.database'),
        ];

        $this->sqliteDatabasePath = database_path(
            'database-seeder-test-'.getmypid().'-'.str_replace('.', '', uniqid('', true)).'.sqlite'
        );
        File::delete($this->sqliteDatabasePath);
        File::put($this->sqliteDatabasePath, '');

        DB::disconnect('sqlite');
        DB::purge('sqlite');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->sqliteDatabasePath,
        ]);

        DB::reconnect('sqlite');
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        DB::purge('sqlite');

        File::delete($this->sqliteDatabasePath);

        config([
            'database.default' => $this->previousDatabaseConfig['default'],
            'database.connections.sqlite.database' => $this->previousDatabaseConfig['sqlite_database'],
        ]);

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
