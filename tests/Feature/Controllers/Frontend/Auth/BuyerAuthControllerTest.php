<?php

namespace Tests\Feature\Controllers\Frontend\Auth;

use Tests\TestCase;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use App\Livewire\Frontend\Auth\Register as FrontendRegister;

class BuyerAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_displays(): void
    {
        $response = $this->get(route('buyer.login'));

        $response->assertStatus(200);
    }

    public function test_registration_form_displays(): void
    {
        $response = $this->get(route('buyer.register'));

        $response->assertStatus(200);
    }

    public function test_buyer_can_register(): void
    {
        Livewire::test(FrontendRegister::class, ['userType' => 'buyer'])
            ->set('email', 'test@gmail.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('buyer.profile.edit'));

        $this->assertDatabaseHas('users_buyers', [
            'email' => 'test@gmail.com',
        ]);
    }

    public function test_buyer_can_login(): void
    {
        $buyer = Buyer::factory()->create([
            'password' => 'password',
            'company_name' => 'Test Co',
            'company_code' => '123456789',
            'address' => 'Test address',
            'phone' => '+37060000000',
        ]);

        Livewire::test(FrontendLogin::class, ['userType' => 'buyer'])
            ->set('email', $buyer->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('buyer.dashboard'));

        $this->assertAuthenticatedAs($buyer, 'buyer');
    }
}

