<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemCommand extends Command
{
    protected $signature = 'system {action : The action to perform (close or open)}';

    public function handle(): int
    {
        return match ((string) $this->argument('action')) {
            'close' => $this->closeSystem(),
            'open' => $this->openSystem(),
            default => $this->invalidAction(),
        };
    }

    private function closeSystem(): int
    {
        $secret = config('app.maintenance.bypass_secret');

        if (! is_string($secret) || $secret === '') {
            $this->error('Set MAINTENANCE_BYPASS_SECRET before enabling maintenance mode.');

            return self::FAILURE;
        }

        $this->info('Closing the system and enabling maintenance mode...');
        $this->callSilent('down', [
            '--secret' => $secret,
            '--render' => 'errors.maintenance',
        ]);
        $this->info('The system is now in maintenance mode.');

        return self::SUCCESS;
    }

    private function openSystem(): int
    {
        $this->info('Opening the system and disabling maintenance mode...');
        $this->callSilent('up');
        $this->info('The system is now live.');

        return self::SUCCESS;
    }

    private function invalidAction(): int
    {
        $this->error('Invalid action. Use "close" to enable maintenance mode or "open" to disable it.');

        return self::FAILURE;
    }
}
