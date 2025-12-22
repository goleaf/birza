<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\EncryptCookies;

class EncryptCookiesTest extends TestCase
{
    public function test_encrypt_cookies_middleware_exists(): void
    {
        $middleware = new EncryptCookies();
        $this->assertInstanceOf(EncryptCookies::class, $middleware);
    }

    public function test_except_array_exists(): void
    {
        $middleware = new EncryptCookies();
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        $except = $property->getValue($middleware);

        $this->assertIsArray($except);
    }
}

