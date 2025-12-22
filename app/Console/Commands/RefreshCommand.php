<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\{
    Artisan,
    Storage,
    File,
    Cache,
    Config
};

class RefreshCommand extends Command
{
    protected $signature = 'refresh';
    protected $description = 'Refresh database, clear caches, and set up storage permissions';

    private array $errors = [];
    private readonly array $stepMessages;

    public function __construct()
    {
        parent::__construct();
        $this->stepMessages = [
            'cache' => 'Clearing all caches...',
            'migrate' => 'Running migrate:fresh --seed...',
            'storage' => 'Setting up storage structure...',
            'permissions' => 'Setting permissions for storage folder...',
            'cleanup' => 'Cleaning up storage files...',
            'ide' => 'Running IDE helper commands...',
            'optimize' => 'Optimizing application...',
            'translations' => 'Importing and finding translations...'
        ];
    }

    public function handle(): int
    {
        $this->displayInitialInfo();

        $steps = $this->getSteps();
        $totalSteps = count($steps);

        foreach ($steps as $index => $step) {
            if (!$this->executeStep($step, $index + 1, $totalSteps)) {
                return Command::FAILURE;
            }
        }

        return $this->handleCompletion();
    }

    private function displayInitialInfo(): void
    {
        $this->info('Starting refresh process...');
        $this->info('This command will:');
        $this->info('- Clear all caches');
        $this->info('- Refresh and seed database');
        $this->info('- Set up storage structure');
        $this->info('- Update folder permissions');
        $this->info('- Clean up storage files');
        $this->info('- Generate IDE helper files');
        $this->info('- Optimize application');
        $this->info('- Import and find translations');
        $this->newLine();
    }

    private function getSteps(): array
    {
        return [
            ['message' => $this->stepMessages['cache'], 'method' => 'clearCaches'],
            [
                'message' => $this->stepMessages['migrate'],
                'method' => fn(): int => $this->call('migrate:fresh', ['--seed' => true, '--force' => true])
            ],
            ['message' => $this->stepMessages['storage'], 'method' => 'setupStorage'],
            ['message' => $this->stepMessages['permissions'], 'method' => 'setFolderPermissions'],
            ['message' => $this->stepMessages['cleanup'], 'method' => 'cleanupStorage'],
            ['message' => $this->stepMessages['ide'], 'method' => 'runIdeHelperCommands'],
            ['message' => $this->stepMessages['optimize'], 'method' => 'optimizeApplication'],
            ['message' => $this->stepMessages['translations'], 'method' => 'handleTranslations']
        ];
    }

    private function executeStep(array $step, int $stepNumber, int $totalSteps): bool
    {
        $this->info("Step {$stepNumber}/{$totalSteps}: {$step['message']}");

        try {
            $result = is_callable($step['method']) ?
                $step['method']() :
                $this->{$step['method']}();

            if ($result !== false) {
                $this->info("✓ Step {$stepNumber} completed successfully");
                return true;
            }
        } catch (\Exception $e) {
            $this->error("Error in step {$stepNumber}: " . $e->getMessage());
            $this->errors[] = "Step {$stepNumber} ({$step['message']}): " . $e->getMessage();
            return false;
        }

        $this->error("Failed to complete step {$stepNumber}");
        return false;
    }

    private function handleStepError(\Exception $e, int $stepNumber, array $step): void
    {
        $this->error("Error in step {$stepNumber}: " . $e->getMessage());
        $this->errors[] = "Step {$stepNumber} ({$step['message']}): " . $e->getMessage();
    }

    private function handleCompletion(): int
    {
        $this->newLine();

        if (empty($this->errors)) {
            $this->info('✓ Refresh completed successfully!');
            $this->info('All systems are ready for development.');
            return Command::SUCCESS;
        }

        $this->error('Refresh completed with errors:');
        foreach ($this->errors as $error) {
            $this->error('- ' . $error);
        }
        return Command::FAILURE;
    }

    private function clearCaches(): bool
    {
        $commands = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'clear-compiled',
            'optimize:clear',
            'view:clear'
        ];

        foreach ($commands as $command) {
            $this->line("Executing: {$command}");
            try {
                $result = $this->call($command);
                if ($result !== 0) {
                    throw new \RuntimeException("Failed to execute {$command}");
                }
                $this->info("✓ {$command} executed successfully");
            } catch (\Exception $e) {
                Cache::flush();
                Config::clear();
                throw $e;
            }
        }
        return true;
    }

    private function setupStorage(): bool
    {
        $directories = [
            'app/public',
            'framework/cache',
            'framework/cache/data',
            'framework/sessions',
            'framework/testing',
            'framework/views',
            'logs',
            'debugbar'
        ];

        foreach ($directories as $directory) {
            $path = storage_path($directory);
            $this->line("Creating directory: {$directory}");
            try {
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0777, true, true);
                }
                $this->info("✓ Created directory: {$directory}");
            } catch (\Exception $e) {
                if (!File::exists($path)) {
                    throw new \RuntimeException("Failed to create directory: {$directory}");
                }
            }
        }

        if (!File::exists(public_path('storage'))) {
            $result = $this->call('storage:link');
            if ($result !== 0) {
                throw new \RuntimeException("Failed to create storage link");
            }
        }

        return true;
    }

    private function setFolderPermissions(): bool
    {
        $paths = [
            base_path('storage'),
            base_path('bootstrap/cache'),
            storage_path('logs'),
            storage_path('framework')
        ];

        foreach ($paths as $path) {
            $this->line("Setting permissions for: {$path}");

            $this->runProcess(
                ['chmod', '-R', '777', $path],
                "Failed to set permissions for {$path}"
            );

            $this->runProcess(
                ['chown', '-R', 'www:www', $path],
                "Failed to set owner for {$path}",
                "✓ Permissions and ownership updated for {$path}"
            );
        }
        return true;
    }

    private function cleanupStorage(): bool
    {
        $cleanupPaths = [
            'logs/*.log' => ['rm', '-f'],
            'framework/cache/data/' => ['find', '-type', 'f', '!', '-name', '.gitignore', '-delete'],
            'debugbar/' => ['find', '-type', 'f', '!', '-name', '.gitignore', '-delete'],
            'framework/views/' => ['find', '-type', 'f', '!', '-name', '.gitignore', '-delete'],
            'framework/sessions/' => ['find', '-type', 'f', '!', '-name', '.gitignore', '-delete']
        ];

        foreach ($cleanupPaths as $relativePath => $command) {
            $path = storage_path($relativePath);
            $this->line("Cleaning: {$path}");
            try {
                $fullCommand = array_merge([$command[0]], [$path], array_slice($command, 1));
                $this->runProcess($fullCommand, "Failed to clean {$path}");
                $this->info("✓ Cleaned: {$path}");
            } catch (\Exception $e) {
                throw new \RuntimeException("Failed to clean path: {$path}");
            }
        }
        return true;
    }

    private function runIdeHelperCommands(): bool
    {
        if (!app()->environment('production')) {
            $commands = [
                'ide-helper:generate' => [],
                'ide-helper:meta' => [],
            ];

            foreach ($commands as $command => $options) {
                $this->line("Executing: {$command}");
                try {
                    $result = $this->call($command, $options);
                    if ($result !== 0) {
                        throw new \RuntimeException("Failed to execute {$command}");
                    }
                    $this->info("✓ {$command} executed successfully");
                } catch (\Exception $e) {
                    if (!app()->environment('local')) {
                        throw $e;
                    }
                }
            }
        }
        return true;
    }

    private function optimizeApplication(): bool
    {
        if (app()->environment('production')) {
            $commands = [
                'optimize' => ['--force' => true],
                'route:cache' => [],
                'config:cache' => [],
                'view:cache' => [],
            ];

            foreach ($commands as $command => $options) {
                $this->line("Executing: {$command}");
                $result = $this->call($command, $options);
                if ($result !== 0) {
                    throw new \RuntimeException("Failed to execute {$command}");
                }
                $this->info("✓ {$command} executed successfully");
            }
        } else {
            $this->call('optimize:clear');
        }
        return true;
    }

    private function handleTranslations(): bool
    {
        $commands = [
            'translations:import' => [],
            'translations:find' => []
        ];

        foreach ($commands as $command => $options) {
            $this->line("Executing: {$command}");
            $result = $this->call($command, $options);
            if ($result !== 0) {
                throw new \RuntimeException("Failed to execute {$command}");
            }
            $this->info("✓ {$command} executed successfully");
        }
        return true;
    }

    private function runProcess(array $command, string $errorMessage, ?string $successMessage = null): void
    {
        $process = new Process($command);
        $process->setTimeout(null);
        $process->run(function (string $type, string $buffer): void {
            if (Process::ERR === $type) {
                $this->error(trim($buffer));
            } else {
                $this->line(trim($buffer));
            }
        });

        if (!$process->isSuccessful()) {
            $error = $process->getErrorOutput();
            $this->error($errorMessage . ($error ? ': ' . $error : ''));
            throw new \RuntimeException($errorMessage);
        } elseif ($successMessage) {
            $this->info($successMessage);
        }
    }
}
