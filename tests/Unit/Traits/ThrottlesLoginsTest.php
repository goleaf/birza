<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use App\Traits\ThrottlesLogins;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class ThrottlesLoginsTest extends TestCase
{
    use RefreshDatabase;

    public function test_throttle_key_generation(): void
    {
        $trait = new class {
            use ThrottlesLogins;
        };

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
        ]);

        $key = $trait->throttleKey($request);

        $this->assertIsString($key);
        $this->assertStringContainsString('test@example.com', $key);
    }

    public function test_has_too_many_login_attempts(): void
    {
        $trait = new class {
            use ThrottlesLogins;
        };

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
        ]);

        $key = $trait->throttleKey($request);
        RateLimiter::hit($key, 60);

        $this->assertTrue($trait->hasTooManyLoginAttempts($request));
    }

    public function test_increment_login_attempts(): void
    {
        $trait = new class {
            use ThrottlesLogins;
        };

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
        ]);

        $trait->incrementLoginAttempts($request);

        $this->assertTrue($trait->hasTooManyLoginAttempts($request));
    }

    public function test_clear_login_attempts(): void
    {
        $trait = new class {
            use ThrottlesLogins;
        };

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
        ]);

        $trait->incrementLoginAttempts($request);
        $trait->clearLoginAttempts($request);

        $this->assertFalse($trait->hasTooManyLoginAttempts($request));
    }
}

