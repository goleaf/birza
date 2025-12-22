<?php

namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use App\Exceptions\Handler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_exception_handler_exists(): void
    {
        $handler = new Handler();
        $this->assertInstanceOf(Handler::class, $handler);
    }

    public function test_exception_handler_has_dont_flash(): void
    {
        $handler = new Handler();
        $reflection = new \ReflectionClass($handler);
        $property = $reflection->getProperty('dontFlash');
        $property->setAccessible(true);

        $dontFlash = $property->getValue($handler);

        $this->assertIsArray($dontFlash);
        $this->assertContains('password', $dontFlash);
        $this->assertContains('password_confirmation', $dontFlash);
    }

    public function test_exception_handler_can_register(): void
    {
        $handler = new Handler();
        $this->assertTrue(method_exists($handler, 'register'));
    }
}

