<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_requires_authentication(): void
    {
        $response = $this->get(route('backend.admin.profile'));

        $response->assertRedirect(route('home'));
    }

    public function test_profile_edit_displays_for_authenticated_admin(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.admin.profile'));

        $response->assertStatus(200);
    }
}

