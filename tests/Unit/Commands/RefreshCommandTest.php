<?php

namespace Tests\Unit\Commands;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class RefreshCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_command_exists(): void
    {
        $this->assertTrue(
            class_exists(\App\Console\Commands\RefreshCommand::class)
        );
    }

    public function test_refresh_command_signature(): void
    {
        $command = new \App\Console\Commands\RefreshCommand();
        $reflection = new \ReflectionClass($command);
        $property = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $this->assertEquals('refresh', $property->getValue($command));
    }

    public function test_refresh_command_description(): void
    {
        $command = new \App\Console\Commands\RefreshCommand();
        $reflection = new \ReflectionClass($command);
        $property = $reflection->getProperty('description');
        $property->setAccessible(true);

        $this->assertNotEmpty($property->getValue($command));
    }
}

