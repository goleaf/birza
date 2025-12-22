<?php

namespace Tests\Feature\Controllers\Frontend\Auth;

use Tests\TestCase;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SellerAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_displays(): void
    {
        $response = $this->get(route('seller.login'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.auth.seller.login');
    }

    public function test_registration_form_displays(): void
    {
        $response = $this->get(route('seller.register'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.auth.seller.register');
    }

    public function test_seller_can_login(): void
    {
        $seller = Seller::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('seller.login'), [
            'email' => $seller->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('seller.dashboard'));
        $this->assertAuthenticatedAs($seller, 'seller');
    }
}

