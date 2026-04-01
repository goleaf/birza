<?php

namespace Tests\Unit;

use Tests\TestCase;

class VoltRemovalTest extends TestCase
{
    public function test_composer_does_not_require_volt(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($composer);
        $this->assertArrayNotHasKey('livewire/volt', $composer['require'] ?? []);
        $this->assertArrayNotHasKey('livewire/volt', $composer['require-dev'] ?? []);
    }

    public function test_runtime_code_contains_no_volt_artifacts(): void
    {
        $forbiddenPatterns = [
            'Livewire\\Volt\\Component',
            'Livewire\\Volt\\Volt',
            'Volt::route(',
            'Volt::test(',
            'VoltServiceProvider',
            'livewire/volt',
        ];

        $paths = [
            app_path(),
            base_path('bootstrap'),
            config_path(),
            resource_path(),
            base_path('routes'),
            base_path('tests'),
            base_path('composer.json'),
            base_path('composer.lock'),
        ];

        $selfPath = realpath(__FILE__);
        $matches = [];

        foreach ($paths as $path) {
            if (! file_exists($path)) {
                continue;
            }

            foreach ($this->filesForScan($path) as $file) {
                if ($selfPath !== false && realpath($file) === $selfPath) {
                    continue;
                }

                $contents = file_get_contents($file);

                if ($contents === false) {
                    continue;
                }

                foreach ($forbiddenPatterns as $pattern) {
                    if (str_contains($contents, $pattern)) {
                        $matches[] = [
                            'file' => $this->relativePath($file),
                            'pattern' => $pattern,
                        ];
                    }
                }
            }
        }

        $this->assertSame([], $matches, 'Forbidden Volt artifacts found: '.json_encode($matches, JSON_THROW_ON_ERROR));
    }

    /**
     * @return list<string>
     */
    private function filesForScan(string $path): array
    {
        if (is_file($path)) {
            return [$path];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $pathname = $file->getPathname();

            if ($this->shouldSkipFile($pathname)) {
                continue;
            }

            $files[] = $pathname;
        }

        return $files;
    }

    private function shouldSkipFile(string $path): bool
    {
        return str_contains($path, DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR)
            || str_contains($path, DIRECTORY_SEPARATOR.'node_modules'.DIRECTORY_SEPARATOR)
            || str_contains($path, DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR)
            || str_contains($path, DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR);
    }

    private function relativePath(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), DIRECTORY_SEPARATOR);
    }
}
