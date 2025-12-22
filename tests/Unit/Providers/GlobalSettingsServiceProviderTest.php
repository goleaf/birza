<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\GlobalSettingsServiceProvider;

class GlobalSettingsServiceProviderTest extends TestCase
{
    public function test_global_settings_service_provider_exists(): void
    {
        $provider = new GlobalSettingsServiceProvider($this->app);
        $this->assertInstanceOf(GlobalSettingsServiceProvider::class, $provider);
    }

    public function test_global_settings_service_provider_can_register(): void
    {
        $provider = new GlobalSettingsServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'register'));
    }

    public function test_global_settings_service_provider_can_boot(): void
    {
        $provider = new GlobalSettingsServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'boot'));
    }
}

