<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;

class AuthServiceProviderTest extends TestCase
{
    public function test_auth_service_provider_exists(): void
    {
        $provider = new AuthServiceProvider($this->app);
        $this->assertInstanceOf(AuthServiceProvider::class, $provider);
    }

    public function test_auth_service_provider_can_register(): void
    {
        $provider = new AuthServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'register'));
    }

    public function test_auth_service_provider_can_boot(): void
    {
        $provider = new AuthServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'boot'));
    }
}

