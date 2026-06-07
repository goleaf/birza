<?php

namespace Tests\Unit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SystemCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Artisan::call('up');

        parent::tearDown();
    }

    public function test_close_fails_when_maintenance_secret_is_not_configured(): void
    {
        config()->set('app.maintenance.bypass_secret');

        $exitCode = Artisan::call('system', ['action' => 'close']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertFalse(app()->isDownForMaintenance());
        $this->assertStringContainsString('MAINTENANCE_BYPASS_SECRET', Artisan::output());
    }

    public function test_close_enables_maintenance_mode_without_exposing_the_secret(): void
    {
        $secret = 'test-maintenance-secret';
        config()->set('app.maintenance.bypass_secret', $secret);

        $exitCode = Artisan::call('system', ['action' => 'close']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertTrue(app()->isDownForMaintenance());
        $this->assertStringNotContainsString($secret, Artisan::output());
    }

    public function test_open_disables_maintenance_mode(): void
    {
        config()->set('app.maintenance.bypass_secret', 'test-maintenance-secret');
        Artisan::call('system', ['action' => 'close']);

        $exitCode = Artisan::call('system', ['action' => 'open']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_invalid_action_returns_failure(): void
    {
        $exitCode = Artisan::call('system', ['action' => 'restart']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertFalse(app()->isDownForMaintenance());
    }
}
