<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\PreventRequestsDuringMaintenance;

class PreventRequestsDuringMaintenanceTest extends TestCase
{
    public function test_prevent_requests_during_maintenance_middleware_exists(): void
    {
        $middleware = new PreventRequestsDuringMaintenance();
        $this->assertInstanceOf(PreventRequestsDuringMaintenance::class, $middleware);
    }

    public function test_except_array_exists(): void
    {
        $middleware = new PreventRequestsDuringMaintenance();
        $reflection = new \ReflectionClass($middleware);
        $property = $reflection->getProperty('except');
        $property->setAccessible(true);

        $except = $property->getValue($middleware);

        $this->assertIsArray($except);
    }
}

