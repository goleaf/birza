<?php

namespace Tests\Feature\Controllers\Frontend\Auth;

use Tests\TestCase;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class BuyerAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_displays(): void
    {
        $response = $this->get(route('buyer.login'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.auth.buyer.login');
    }

    public function test_registration_form_displays(): void
    {
        $response = $this->get(route('buyer.register'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.auth.buyer.register');
    }

    public function test_buyer_can_register(): void
    {
        $response = $this->post(route('buyer.register'), [
            'name' => 'Test Buyer',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users_buyers', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_buyer_can_login(): void
    {
        $buyer = Buyer::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('buyer.login'), [
            'email' => $buyer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('buyer.dashboard'));
        $this->assertAuthenticatedAs($buyer, 'buyer');
    }
}

