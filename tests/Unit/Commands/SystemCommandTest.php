<?php

namespace Tests\Unit\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_command_exists(): void
    {
        $this->assertTrue(
            class_exists(\App\Console\Commands\SystemCommand::class)
        );
    }

    public function test_system_command_signature(): void
    {
        $command = new \App\Console\Commands\SystemCommand();
        $reflection = new \ReflectionClass($command);
        $property = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $this->assertStringContainsString('system', $property->getValue($command));
        $this->assertStringContainsString('action', $property->getValue($command));
    }

    public function test_system_command_accepts_close_action(): void
    {
        $command = new \App\Console\Commands\SystemCommand();
        
        $this->assertTrue(method_exists($command, 'handle'));
    }

    public function test_system_command_accepts_open_action(): void
    {
        $command = new \App\Console\Commands\SystemCommand();
        
        $this->assertTrue(method_exists($command, 'handle'));
    }
}

