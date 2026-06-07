<?php

namespace Tests\Feature\Controllers\Frontend\Auth;

use App\Livewire\Frontend\Auth\ForgotPassword;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use App\Livewire\Frontend\Auth\Register as FrontendRegister;
use App\Livewire\Frontend\Auth\VerificationNotice;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SellerAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_displays(): void
    {
        $response = $this->get(route('seller.login'));

        $response->assertStatus(200)
            ->assertSeeLivewire(FrontendLogin::class);
    }

    public function test_registration_form_displays(): void
    {
        $response = $this->get(route('seller.register'));

        $response->assertStatus(200)
            ->assertSeeLivewire(FrontendRegister::class);
    }

    public function test_seller_registration_uses_a_secure_verification_token(): void
    {
        Mail::fake();

        Livewire::test(FrontendRegister::class, ['userType' => 'seller'])
            ->set('email', 'secure-seller@gmail.com')
            ->set('password', 'StrongPassword123!')
            ->set('password_confirmation', 'StrongPassword123!')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('seller.register.success'));

        $token = Seller::query()
            ->where('email', 'secure-seller@gmail.com')
            ->value('remember_token');

        $this->assertIsString($token);
        $this->assertSame(64, strlen($token));
    }

    public function test_password_reset_uses_a_secure_random_token(): void
    {
        Mail::fake();
        $seller = Seller::factory()->create();

        Livewire::test(ForgotPassword::class, ['userType' => 'seller'])
            ->set('email', $seller->email)
            ->call('sendResetLink')
            ->assertHasNoErrors();

        $token = $seller->refresh()->remember_token;

        $this->assertIsString($token);
        $this->assertSame(64, strlen($token));
    }

    public function test_verification_resend_uses_a_non_reversible_rate_limit_key(): void
    {
        Mail::fake();
        RateLimiter::clear('verify:seller:'.hash('sha256', 'pending-seller@example.com'));
        $seller = Seller::factory()->create([
            'email' => 'pending-seller@example.com',
            'is_verified' => false,
        ]);

        Livewire::test(VerificationNotice::class, ['userType' => 'seller'])
            ->set('email', $seller->email)
            ->call('resendVerification')
            ->assertHasNoErrors();

        $rateLimitKey = 'verify:seller:'.hash('sha256', $seller->email);

        $this->assertSame(1, RateLimiter::attempts($rateLimitKey));
        $this->assertSame(64, strlen((string) $seller->refresh()->remember_token));
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
