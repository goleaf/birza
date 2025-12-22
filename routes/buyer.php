<?php

use App\Http\Controllers\Frontend\Auth\AuthController;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use App\Livewire\Frontend\Auth\Register as FrontendRegister;
use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Livewire\Frontend\Buyer\Dashboard as BuyerDashboard;
use App\Livewire\Frontend\Buyer\Orders\Index as BuyerOrdersIndex;
use App\Livewire\Frontend\Buyer\Orders\Show as BuyerOrdersShow;
use App\Livewire\Frontend\Buyer\Products\Index as BuyerProductsIndex;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductsShow;
use App\Livewire\Frontend\Buyer\Profile\Edit as BuyerProfileEdit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'buyer', 'as' => 'buyer.'], function () {
    // Guest routes
    Route::middleware('guest:buyer')->group(function () {
        Route::get('/login', FrontendLogin::class)->name('login');
        Route::get('/register', FrontendRegister::class)->name('register');
        Route::get('/register/success', [AuthController::class, 'showRegistrationSuccess'])->name('register.success');

        // Password Reset Routes
        Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{hash}', [AuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

        // Email verification routes
        Route::get('/email/verify/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
        Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
        Route::post('/email/resend', [AuthController::class, 'resend'])->name('verification.resend');
    });

    // Authenticated routes
    Route::middleware('auth:buyer')->group(function () {
        Route::get('/dashboard', BuyerDashboard::class)->name('dashboard');
        Route::get('/profile', BuyerProfileEdit::class)->name('profile.edit');
        Route::post('/logout', function (Request $request) {
            Auth::guard('buyer')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', __('messages.logout_success'));
        })->name('logout');

        Route::get('/products', BuyerProductsIndex::class)->name('products.index');
        Route::get('/products/{product}', BuyerProductsShow::class)->name('products.show');

        Route::get('/cart', BuyerCartIndex::class)->name('cart.index');

        Route::get('/orders', BuyerOrdersIndex::class)->name('orders.index');
        Route::get('/orders/{order}', BuyerOrdersShow::class)->name('orders.show');

    });
});
