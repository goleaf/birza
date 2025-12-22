<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\TrustHosts;

class TrustHostsTest extends TestCase
{
    public function test_trust_hosts_middleware_exists(): void
    {
        $middleware = new TrustHosts();
        $this->assertInstanceOf(TrustHosts::class, $middleware);
    }
}

