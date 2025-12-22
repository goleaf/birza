<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;

class AppServiceProviderTest extends TestCase
{
    public function test_app_service_provider_exists(): void
    {
        $provider = new AppServiceProvider($this->app);
        $this->assertInstanceOf(AppServiceProvider::class, $provider);
    }

    public function test_app_service_provider_can_register(): void
    {
        $provider = new AppServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'register'));
    }

    public function test_app_service_provider_can_boot(): void
    {
        $provider = new AppServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'boot'));
    }
}

