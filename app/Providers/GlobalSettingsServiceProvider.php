<?php

namespace App\Providers;

use App\Models\GlobalSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class GlobalSettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        try {
            $portalAdditionalPrice = Cache::remember('portal_additional_price', 60, function() {
                $settings = GlobalSettings::first();
                return $settings ? $settings->portal_additional_price : 0;
            });

            View::share('portalAdditionalPrice', $portalAdditionalPrice);
        } catch (\Exception $e) {
            // Handle cases where database/tables don't exist yet (e.g., during migrations)
            View::share('portalAdditionalPrice', 0);
        }
    }
}
