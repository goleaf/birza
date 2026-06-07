<?php

namespace Tests\Feature\Controllers\Backend\Auth;

use App\Livewire\Backend\Auth\Login as AdminLogin;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_displays(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200)
            ->assertSeeLivewire(AdminLogin::class)
            ->assertSee(__('backend_auth_portal_title'))
            ->assertSee(__('backend_auth_portal_badge'))
            ->assertSee(__('common_email_address'))
            ->assertSee(__('common_password'))
            ->assertDontSee(__('backend_spotlight_open'));
    }

    public function test_admin_root_redirects_guest_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_root_redirects_authenticated_admin_to_dashboard(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_redirects_if_already_authenticated(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create([
            'password' => \Hash::make('password'),
        ]);

        Livewire::test(AdminLogin::class)
            ->set('email', $admin->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_login_with_invalid_credentials(): void
    {
        $admin = Admin::factory()->create();

        Livewire::test(AdminLogin::class)
            ->set('email', $admin->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest('admin');
    }

    public function test_logout(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }
}
