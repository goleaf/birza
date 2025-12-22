<?php

namespace Tests\Feature\Controllers\Backend\Auth;

use Tests\TestCase;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_displays(): void
    {
        $response = $this->get(route('backend.login'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.auth.login');
    }

    public function test_login_redirects_if_already_authenticated(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.login'));

        $response->assertRedirect(route('backend.dashboard'));
    }

    public function test_login_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create([
            'password' => \Hash::make('password'),
        ]);

        $response = $this->post(route('backend.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('backend.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_login_with_invalid_credentials(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->post(route('backend.login'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_logout(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('backend.logout'));

        $response->assertRedirect(route('backend.login'));
        $this->assertGuest('admin');
    }
}

