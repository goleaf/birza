<?php

namespace Tests\Feature\Marketplace;

use App\Livewire\Backend\Auth\Login as AdminLogin;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class AuthenticationFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_guest_can_open_public_pages(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('buyer.login'))->assertOk();
        $this->get(route('buyer.register'))->assertOk();
        $this->get(route('seller.login'))->assertOk();
        $this->get(route('seller.register'))->assertOk();
        $this->get(route('admin.login'))->assertOk();
    }

    public function test_guest_cannot_open_private_pages(): void
    {
        $this->get(route('buyer.dashboard'))->assertRedirect(route('home'));
        $this->get(route('seller.dashboard'))->assertRedirect(route('home'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('home'));
    }

    public function test_buyer_can_login_and_logout(): void
    {
        $buyer = $this->createBuyer(['email' => 'buyer@example.test']);

        Livewire::test(FrontendLogin::class, ['userType' => 'buyer'])
            ->set('email', $buyer->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('buyer.dashboard'));

        $this->assertAuthenticatedAs($buyer, 'buyer');

        $this->post(route('buyer.logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', __('messages_logout_success'));

        $this->assertGuest('buyer');
    }

    public function test_invalid_buyer_login_fails(): void
    {
        $buyer = $this->createBuyer(['email' => 'buyer-invalid@example.test']);

        Livewire::test(FrontendLogin::class, ['userType' => 'buyer'])
            ->set('email', $buyer->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest('buyer');
    }

    public function test_inactive_buyer_cannot_login_or_access_protected_pages(): void
    {
        $buyer = $this->createBuyer([
            'email' => 'inactive-buyer@example.test',
            'is_active' => false,
        ]);

        Livewire::test(FrontendLogin::class, ['userType' => 'buyer'])
            ->set('email', $buyer->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest('buyer');

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.dashboard'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', __('messages_account_inactive'));

        $this->assertGuest('buyer');
    }

    public function test_unverified_seller_cannot_login(): void
    {
        $seller = $this->createSeller([
            'email' => 'unverified-seller@example.test',
            'is_verified' => false,
        ]);

        Livewire::test(FrontendLogin::class, ['userType' => 'seller'])
            ->set('email', $seller->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest('seller');
    }

    public function test_inactive_seller_cannot_access_protected_pages(): void
    {
        $seller = $this->createSeller(['is_active' => false]);

        $this->actingAs($seller, 'seller')
            ->get(route('seller.dashboard'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', __('messages_account_inactive'));

        $this->assertGuest('seller');
    }

    public function test_inactive_admin_cannot_login_or_access_protected_pages(): void
    {
        $admin = $this->createAdmin([
            'email' => 'inactive-admin@example.test',
            'is_active' => false,
        ]);

        Livewire::test(AdminLogin::class)
            ->set('email', $admin->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest('admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', __('messages_account_inactive'));

        $this->assertGuest('admin');
    }
}
