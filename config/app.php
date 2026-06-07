<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\GlobalSettingsServiceProvider;
use App\Providers\UserGuardServiceProvider;
use App\Providers\ViewServiceProvider;
use Barryvdh\Debugbar\ServiceProvider as DebugbarServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Kettasoft\Filterable\Providers\FilterableServiceProvider;

return [

    'name' => env('APP_NAME', 'Laravel'),

    'vat_rate' => env('VAT_RATE', 0.21),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    'timezone' => 'UTC',

    'locale' => 'lt',

    'locales' => [
        'lt',
        'en',
    ],

    'fallback_locale' => 'en',

    'faker_locale' => 'en_US',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'maintenance' => [
        'driver' => 'file',
        'bypass_secret' => env('MAINTENANCE_BYPASS_SECRET'),
    ],

    'providers' => ServiceProvider::defaultProviders()->merge(array_filter([
        AppServiceProvider::class,
        AuthServiceProvider::class,
        EventServiceProvider::class,
        env('APP_ENV', 'production') === 'local' && (bool) env('DEBUGBAR_ENABLED', false) && class_exists(DebugbarServiceProvider::class)
            ? DebugbarServiceProvider::class
            : null,
        env('APP_ENV', 'production') === 'local' && class_exists(IdeHelperServiceProvider::class)
            ? IdeHelperServiceProvider::class
            : null,
        UserGuardServiceProvider::class,
        ViewServiceProvider::class,
        GlobalSettingsServiceProvider::class,
        FilterableServiceProvider::class,

    ]))->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
    ])->toArray(),

];
