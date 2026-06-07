<?php

namespace App\Providers;

use App\Enums\MarketplaceRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class UserGuardServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('*', function ($view) {
            $guard = match (true) {
                Auth::guard(MarketplaceRole::Buyer->guard())->check() => MarketplaceRole::Buyer->value,
                Auth::guard(MarketplaceRole::Seller->guard())->check() => MarketplaceRole::Seller->value,
                default => null,
            };

            $user = null;
            if ($guard) {
                $user = Auth::guard($guard)->user();
            }

            $view->with([
                'guard' => $guard,
                'user' => $user
            ]);
        });
    }
}
