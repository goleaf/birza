<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class DebugbarConfigurationTest extends TestCase
{
    /**
     * @var array<string, array{exists: bool, value: string|false, env_exists: bool, env_value: string|null, server_exists: bool, server_value: string|null}>
     */
    private array $originalEnvironment = [];

    protected function tearDown(): void
    {
        foreach ($this->originalEnvironment as $key => $state) {
            if ($state['exists']) {
                putenv($key.'='.$state['value']);
            } else {
                putenv($key);
            }

            if ($state['env_exists']) {
                $_ENV[$key] = $state['env_value'];
            } else {
                unset($_ENV[$key]);
            }

            if ($state['server_exists']) {
                $_SERVER[$key] = $state['server_value'];
            } else {
                unset($_SERVER[$key]);
            }
        }

        parent::tearDown();
    }

    public function test_debugbar_is_disabled_in_production_even_when_enabled_env_is_true(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'production');
        $this->setEnvironmentVariable('DEBUGBAR_ENABLED', 'true');

        $this->assertFalse($this->debugbarEnabled());
    }

    public function test_debugbar_is_disabled_by_default_for_local_development(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'local');
        $this->setEnvironmentVariable('DEBUGBAR_ENABLED', null);

        $this->assertFalse($this->debugbarEnabled());
    }

    public function test_debugbar_can_be_enabled_for_local_development(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'local');
        $this->setEnvironmentVariable('DEBUGBAR_ENABLED', 'true');

        $this->assertTrue($this->debugbarEnabled());
    }

    public function test_debugbar_service_provider_is_not_registered_in_production(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'production');
        $this->setEnvironmentVariable('DEBUGBAR_ENABLED', 'true');

        $providers = $this->appProviders();

        $this->assertNotContains('Barryvdh\\Debugbar\\ServiceProvider', $providers);
    }

    public function test_debugbar_service_provider_is_not_registered_by_default_for_local_development(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'local');
        $this->setEnvironmentVariable('DEBUGBAR_ENABLED', null);

        $providers = $this->appProviders();

        $this->assertNotContains('Barryvdh\\Debugbar\\ServiceProvider', $providers);
    }

    public function test_debugbar_service_provider_is_registered_for_local_opt_in(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'local');
        $this->setEnvironmentVariable('DEBUGBAR_ENABLED', 'true');

        $providers = $this->appProviders();

        $this->assertContains('Barryvdh\\Debugbar\\ServiceProvider', $providers);
    }

    public function test_development_only_providers_are_not_forced_in_production(): void
    {
        $this->setEnvironmentVariable('APP_ENV', 'production');

        $providers = $this->appProviders();

        $this->assertNotContains('Barryvdh\\LaravelIdeHelper\\IdeHelperServiceProvider', $providers);
    }

    private function debugbarEnabled(): bool
    {
        $config = require config_path('debugbar.php');

        return $config['enabled'];
    }

    /**
     * @return array<int, class-string>
     */
    private function appProviders(): array
    {
        $config = require config_path('app.php');

        return $config['providers'];
    }

    private function setEnvironmentVariable(string $key, ?string $value): void
    {
        if (! array_key_exists($key, $this->originalEnvironment)) {
            $this->originalEnvironment[$key] = [
                'exists' => getenv($key) !== false,
                'value' => getenv($key),
                'env_exists' => array_key_exists($key, $_ENV),
                'env_value' => $_ENV[$key] ?? null,
                'server_exists' => array_key_exists($key, $_SERVER),
                'server_value' => $_SERVER[$key] ?? null,
            ];
        }

        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
