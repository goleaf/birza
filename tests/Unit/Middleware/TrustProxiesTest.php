<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

class TrustProxiesTest extends TestCase
{
    public function test_trust_proxies_middleware_exists(): void
    {
        $middleware = new TrustProxies();
        $this->assertInstanceOf(TrustProxies::class, $middleware);
    }

    public function test_headers_are_set(): void
    {
        $middleware = new TrustProxies();
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);

        $headers = $property->getValue($middleware);

        $this->assertIsInt($headers);
        $this->assertGreaterThan(0, $headers);
    }
}

