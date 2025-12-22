<?php

namespace App\Providers;

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
                Auth::guard('buyer')->check() => 'buyer',
                Auth::guard('seller')->check() => 'seller',
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
