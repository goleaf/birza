<?php

namespace Tests\Unit\Console;

use Tests\TestCase;
use App\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;

class KernelTest extends TestCase
{
    public function test_kernel_exists(): void
    {
        $this->assertTrue(class_exists(Kernel::class));
    }

    public function test_kernel_extends_console_kernel(): void
    {
        $kernel = new Kernel($this->app, $this->app->make('events'));
        
        $this->assertInstanceOf(\Illuminate\Foundation\Console\Kernel::class, $kernel);
    }

    public function test_kernel_has_schedule_method(): void
    {
        $kernel = new Kernel($this->app, $this->app->make('events'));
        
        $reflection = new \ReflectionClass($kernel);
        $this->assertTrue($reflection->hasMethod('schedule'));
    }

    public function test_kernel_has_commands_method(): void
    {
        $kernel = new Kernel($this->app, $this->app->make('events'));
        
        $reflection = new \ReflectionClass($kernel);
        $this->assertTrue($reflection->hasMethod('commands'));
    }

    public function test_kernel_loads_commands(): void
    {
        $kernel = new Kernel($this->app, $this->app->make('events'));
        
        $reflection = new \ReflectionClass($kernel);
        $method = $reflection->getMethod('commands');
        $method->setAccessible(true);
        
        // Should not throw exception
        $method->invoke($kernel);
        
        $this->assertTrue(true);
    }
}

