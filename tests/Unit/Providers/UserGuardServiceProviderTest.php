<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\UserGuardServiceProvider;

class UserGuardServiceProviderTest extends TestCase
{
    public function test_user_guard_service_provider_exists(): void
    {
        $provider = new UserGuardServiceProvider($this->app);
        $this->assertInstanceOf(UserGuardServiceProvider::class, $provider);
    }

    public function test_user_guard_service_provider_can_register(): void
    {
        $provider = new UserGuardServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'register'));
    }

    public function test_user_guard_service_provider_can_boot(): void
    {
        $provider = new UserGuardServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'boot'));
    }
}

