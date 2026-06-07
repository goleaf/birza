<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;
use LukePOLO\LaraCart\Facades\LaraCart;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.frontend.header', function (ViewContract $view): void {
            $view->with('cartItemsCount', $this->buyerCartItemsCount());
        });
    }

    private function buyerCartItemsCount(): int
    {
        if (! Auth::guard('buyer')->check()) {
            return 0;
        }

        return (int) LaraCart::count();
    }
}
