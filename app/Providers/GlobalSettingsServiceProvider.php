<?php

namespace App\Providers;

use App\Models\GlobalSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GlobalSettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('*', function ($view): void {
            try {
                $portalAdditionalPrice = Cache::remember('portal_additional_price', 60, function () {
                    $settings = GlobalSettings::first();

                    return $settings ? $settings->portal_additional_price : 0;
                });

                $adminThemeColors = Cache::remember('admin_theme_colors', 60, function () {
                    $settings = GlobalSettings::first();

                    return $settings?->adminThemeColors() ?? GlobalSettings::adminThemeDefaults();
                });

                $view->with([
                    'portalAdditionalPrice' => $portalAdditionalPrice,
                    'adminThemeColors' => $adminThemeColors,
                ]);
            } catch (\Exception $e) {
                // Handle cases where database/tables don't exist yet (e.g., during migrations)
                $view->with([
                    'portalAdditionalPrice' => 0,
                    'adminThemeColors' => GlobalSettings::adminThemeDefaults(),
                ]);
            }
        });
    }
}
