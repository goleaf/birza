<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\RouteServiceProvider;

class RouteServiceProviderTest extends TestCase
{
    public function test_route_service_provider_exists(): void
    {
        $provider = new RouteServiceProvider($this->app);
        $this->assertInstanceOf(RouteServiceProvider::class, $provider);
    }

    public function test_route_service_provider_has_home_constant(): void
    {
        $this->assertEquals('/home', RouteServiceProvider::HOME);
    }

    public function test_route_service_provider_can_boot(): void
    {
        $provider = new RouteServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'boot'));
    }
}

