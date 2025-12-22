<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_password_is_hashed(): void
    {
        $admin = Admin::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $admin->password);
        $this->assertTrue(\Hash::check('plaintext', $admin->password));
    }

    public function test_admin_fillable_attributes(): void
    {
        $admin = new Admin();
        $fillable = $admin->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }
}

