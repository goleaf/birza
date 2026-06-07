<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Admin\Profile as AdminProfile;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_requires_authentication(): void
    {
        $response = $this->get(route('admin.profile'));

        $response->assertRedirect(route('home'));
    }

    public function test_profile_edit_displays_for_authenticated_admin(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.profile'));

        $response->assertStatus(200)
            ->assertSeeLivewire(AdminProfile::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('profile'))
            ->assertSee(__('auth_name'))
            ->assertSee(__('auth_current_password'));
    }
}
