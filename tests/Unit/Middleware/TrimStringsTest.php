<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\TrimStrings;

class TrimStringsTest extends TestCase
{
    public function test_trim_strings_middleware_exists(): void
    {
        $middleware = new TrimStrings();
        $this->assertInstanceOf(TrimStrings::class, $middleware);
    }

    public function test_except_array_exists(): void
    {
        $middleware = new TrimStrings();
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        $except = $property->getValue($middleware);

        $this->assertIsArray($except);
        $this->assertContains('current_password', $except);
        $this->assertContains('password', $except);
        $this->assertContains('password_confirmation', $except);
    }
}

