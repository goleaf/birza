<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system {action : The action to perform (close or open)}';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $secret = 'prus'; // Define your maintenance bypass secret here

        if ($action === 'close') {
            $this->info('Closing the system and enabling maintenance mode...');

            // Put the application into maintenance mode with a secret
            $this->callSilent('down', [
                '--secret' => "$secret", 
                '--render' => "errors.maintenance",
            ]);

            $this->info('The system is now in maintenance mode.');
            $this->info("Access with the secret: https://birza.prus.dev/{$secret}");
        } elseif ($action === 'open') {
            $this->info('Opening the system and disabling maintenance mode...');
            $this->callSilent('up');
            $this->info('The system is now live.');
        } else {
            $this->error('Invalid action. Use "close" to enable maintenance mode or "open" to disable it.');
        }

        return 0;
    }
}
