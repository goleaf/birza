<?php

namespace Tests\Feature\Seeders;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_is_idempotent_and_keeps_stable_credentials(): void
    {
        $this->seed(AdminSeeder::class);

        $admin = Admin::query()->firstOrFail();
        $initialPasswordHash = $admin->password;

        $this->assertSame(1, Admin::query()->count());
        $this->assertSame('admin@admin.com', $admin->email);
        $this->assertTrue(Hash::check('password', $admin->password));

        $this->seed(AdminSeeder::class);

        $admin->refresh();

        $this->assertSame(1, Admin::query()->count());
        $this->assertSame($initialPasswordHash, $admin->password);
    }
}
