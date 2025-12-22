<?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Providers\EventServiceProvider;

class EventServiceProviderTest extends TestCase
{
    public function test_event_service_provider_exists(): void
    {
        $provider = new EventServiceProvider($this->app);
        $this->assertInstanceOf(EventServiceProvider::class, $provider);
    }

    public function test_event_service_provider_can_register(): void
    {
        $provider = new EventServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'register'));
    }

    public function test_event_service_provider_can_boot(): void
    {
        $provider = new EventServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'boot'));
    }
}

