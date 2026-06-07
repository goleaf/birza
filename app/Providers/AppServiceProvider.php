<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Configure translations to use the lang/ folder
        $this->app->useLangPath(base_path('lang'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Keep Laravel "namespaced" vendor views working without resources/views/vendor/*
        // by mapping them to our top-level resources/views/* folders.
        View::replaceNamespace('pagination', resource_path('views/pagination'));
        View::replaceNamespace('notifications', resource_path('views/notifications'));

        // Ensure Livewire's bundled Alpine boots AFTER WireUI's deferred scripts have registered directives.
        // WireUI scripts are rendered with `defer`, so we also defer Livewire's script tag.
        Livewire::useScriptTagAttributes([
            'defer' => true,
        ]);

        Product::observe(ProductObserver::class);
    }
}
