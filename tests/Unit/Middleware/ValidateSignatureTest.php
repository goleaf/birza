<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\ValidateSignature;

class ValidateSignatureTest extends TestCase
{
    public function test_validate_signature_middleware_exists(): void
    {
        $middleware = new ValidateSignature();
        $this->assertInstanceOf(ValidateSignature::class, $middleware);
    }

    public function test_except_array_exists(): void
    {
        $middleware = new ValidateSignature();
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        $except = $property->getValue($middleware);

        $this->assertIsArray($except);
    }
}

