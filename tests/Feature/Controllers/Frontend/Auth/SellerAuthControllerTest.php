<?php

namespace Tests\Feature\Controllers\Frontend\Auth;

use Tests\TestCase;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;

class SellerAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_displays(): void
    {
        $response = $this->get(route('seller.login'));

        $response->assertStatus(200);
    }

    public function test_registration_form_displays(): void
    {
        $response = $this->get(route('seller.register'));

        $response->assertStatus(200);
    }

    public function test_seller_can_login(): void
    {
        $seller = Seller::factory()->create([
            'password' => 'password',
            'is_verified' => true,
            'is_active' => true,
            'company_name' => 'Test Co',
            'company_code' => '123456789',
            'address' => 'Test address',
            'phone' => '+37060000000',
        ]);

        Livewire::test(FrontendLogin::class, ['userType' => 'seller'])
            ->set('email', $seller->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('seller.dashboard'));

        $this->assertAuthenticatedAs($seller, 'seller');
    }
}

