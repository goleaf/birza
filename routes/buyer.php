<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Frontend\Auth\ForgotPassword as FrontendForgotPassword;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use App\Livewire\Frontend\Auth\Register as FrontendRegister;
use App\Livewire\Frontend\Auth\RegisterSuccess as FrontendRegisterSuccess;
use App\Livewire\Frontend\Auth\ResetPassword as FrontendResetPassword;
use App\Livewire\Frontend\Auth\VerificationNotice as FrontendVerificationNotice;
use App\Livewire\Frontend\Auth\VerifyEmail as FrontendVerifyEmail;
use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Livewire\Frontend\Buyer\Dashboard as BuyerDashboard;
use App\Livewire\Frontend\Buyer\Orders\Index as BuyerOrdersIndex;
use App\Livewire\Frontend\Buyer\Orders\Show as BuyerOrdersShow;
use App\Livewire\Frontend\Buyer\Products\Index as BuyerProductsIndex;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductsShow;
use App\Livewire\Frontend\Buyer\Profile\Edit as BuyerProfileEdit;
use App\Livewire\Frontend\Buyer\StockAlerts\Index as BuyerStockAlertsIndex;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'buyer', 'as' => 'buyer.'], function () {
    // Guest routes
    Route::middleware('guest:buyer')->group(function () {
        Route::livewire('/login', FrontendLogin::class)->name('login');
        Route::livewire('/register', FrontendRegister::class)->name('register');
        Route::livewire('/register/success', FrontendRegisterSuccess::class)->name('register.success');

        // Password reset
        Route::livewire('/forgot-password', FrontendForgotPassword::class)->name('password.request');
        Route::livewire('/reset-password/{hash}', FrontendResetPassword::class)->name('password.reset');

        // Email verification routes
        Route::livewire('/email/verify/{hash}', FrontendVerifyEmail::class)->name('verification.verify')->middleware('throttle:6,1');
        Route::livewire('/email/verify', FrontendVerificationNotice::class)->name('verification.notice');
    });

    // Authenticated routes
    Route::middleware('auth:buyer')->group(function () {
        Route::livewire('/dashboard', BuyerDashboard::class)->name('dashboard');
        Route::livewire('/profile', BuyerProfileEdit::class)->name('profile.edit');
        Route::post('/logout', LogoutController::class)
            ->defaults('guard', 'buyer')
            ->defaults('redirectRoute', 'home')
            ->defaults('flashMessage', 'messages_logout_success')
            ->name('logout');

        Route::livewire('/products', BuyerProductsIndex::class)->name('products.index');
        Route::livewire('/products/{product}', BuyerProductsShow::class)->name('products.show');

        Route::livewire('/cart', BuyerCartIndex::class)->name('cart.index');

        Route::livewire('/orders', BuyerOrdersIndex::class)->name('orders.index');
        Route::livewire('/orders/{order}', BuyerOrdersShow::class)->name('orders.show');
        Route::livewire('/stock-alerts', BuyerStockAlertsIndex::class)->name('stock-alerts.index');
    });
});
