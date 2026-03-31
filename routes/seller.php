<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Frontend\Auth\ForgotPassword as FrontendForgotPassword;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use App\Livewire\Frontend\Auth\Register as FrontendRegister;
use App\Livewire\Frontend\Auth\RegisterSuccess as FrontendRegisterSuccess;
use App\Livewire\Frontend\Auth\ResetPassword as FrontendResetPassword;
use App\Livewire\Frontend\Auth\VerificationNotice as FrontendVerificationNotice;
use App\Livewire\Frontend\Auth\VerifyEmail as FrontendVerifyEmail;
use App\Livewire\Frontend\Seller\Dashboard as SellerDashboard;
use App\Livewire\Frontend\Seller\Orders\Index as SellerOrdersIndex;
use App\Livewire\Frontend\Seller\Orders\Show as SellerOrdersShow;
use App\Livewire\Frontend\Seller\Products\Create as SellerProductsCreate;
use App\Livewire\Frontend\Seller\Products\Edit as SellerProductsEdit;
use App\Livewire\Frontend\Seller\Products\Index as SellerProductsIndex;
use App\Livewire\Frontend\Seller\Profile\Edit as SellerProfileEdit;
use App\Livewire\Frontend\Seller\Transactions\Index as SellerTransactionsIndex;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'seller', 'as' => 'seller.'], function () {
    // Guest routes
    Route::middleware('guest:seller')->group(function () {
        Route::get('/login', FrontendLogin::class)->name('login');
        Route::get('/register', FrontendRegister::class)->name('register');
        Route::get('/register/success', FrontendRegisterSuccess::class)->name('register.success');

        // Password reset
        Route::get('/forgot-password', FrontendForgotPassword::class)->name('password.request');
        Route::get('/reset-password/{hash}', FrontendResetPassword::class)->name('password.reset');

        // Email verification routes
        Route::get('/email/verify/{hash}', FrontendVerifyEmail::class)->name('verification.verify')->middleware('throttle:6,1');
        Route::get('/email/verify', FrontendVerificationNotice::class)->name('verification.notice');
    });

    // Authenticated routes
    Route::middleware('auth:seller')->group(function () {
        Route::get('/dashboard', SellerDashboard::class)->name('dashboard');
        Route::get('/profile', SellerProfileEdit::class)->name('profile.edit');
        Route::post('/logout', LogoutController::class)
            ->defaults('guard', 'seller')
            ->defaults('redirectRoute', 'home')
            ->defaults('flashMessage', 'messages_logout_success')
            ->name('logout');

        // Seller specific routes
        Route::get('/products', SellerProductsIndex::class)->name('products.index');
        Route::get('/products/create/{categoryId}', SellerProductsCreate::class)->name('products.create');
        Route::get('/products/{product}/edit', SellerProductsEdit::class)->name('products.edit');

        // Orders
        Route::get('/orders', SellerOrdersIndex::class)->name('orders.index');
        Route::get('/orders/{order}', SellerOrdersShow::class)->name('orders.show');

        // Transactions
        Route::get('/transactions', SellerTransactionsIndex::class)->name('transactions.index');
    });
});
