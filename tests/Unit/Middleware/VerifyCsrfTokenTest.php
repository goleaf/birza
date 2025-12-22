<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\VerifyCsrfToken;

class VerifyCsrfTokenTest extends TestCase
{
    public function test_csrf_token_middleware_exists(): void
    {
        $middleware = new VerifyCsrfToken();
        $this->assertInstanceOf(VerifyCsrfToken::class, $middleware);
    }

    public function test_except_array_exists(): void
    {
        $middleware = new VerifyCsrfToken();
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        $except = $property->getValue($middleware);

        $this->assertIsArray($except);
    }
}

