<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\BroadcastServiceProvider;

class BroadcastServiceProviderTest extends TestCase
{
    public function test_broadcast_service_provider_exists(): void
    {
        $provider = new BroadcastServiceProvider($this->app);
        $this->assertInstanceOf(BroadcastServiceProvider::class, $provider);
    }

    public function test_broadcast_service_provider_can_register(): void
    {
        $provider = new BroadcastServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'register'));
    }

    public function test_broadcast_service_provider_can_boot(): void
    {
        $provider = new BroadcastServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'boot'));
    }
}

