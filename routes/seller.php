<?php

use App\Http\Controllers\Frontend\Auth\AuthController;
use App\Http\Controllers\Frontend\Seller\ProfileController as SellerProfileController;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use App\Livewire\Frontend\Auth\Register as FrontendRegister;
use App\Livewire\Frontend\Seller\Dashboard as SellerDashboard;
use App\Livewire\Frontend\Seller\Orders\Index as SellerOrdersIndex;
use App\Livewire\Frontend\Seller\Orders\Show as SellerOrdersShow;
use App\Livewire\Frontend\Seller\Products\Create as SellerProductsCreate;
use App\Livewire\Frontend\Seller\Products\Edit as SellerProductsEdit;
use App\Livewire\Frontend\Seller\Products\Index as SellerProductsIndex;
use App\Livewire\Frontend\Seller\Profile\Edit as SellerProfileEdit;
use App\Livewire\Frontend\Seller\Transactions\Index as SellerTransactionsIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'seller', 'as' => 'seller.'], function () {
    // Guest routes
    Route::middleware('guest:seller')->group(function () {
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
    Route::middleware('auth:seller')->group(function () {
        Route::get('/dashboard', SellerDashboard::class)->name('dashboard');
        Route::get('/profile', SellerProfileEdit::class)->name('profile.edit');
        Route::put('/profile', [SellerProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [SellerProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/logout', function (Request $request) {
            Auth::guard('seller')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', __('messages.logout_success'));
        })->name('logout');

        // Seller specific routes
        Route::get('/products', SellerProductsIndex::class)->name('products.index');
        Route::get('/products/create/{categoryId}', SellerProductsCreate::class)->name('products.create');
        Route::get('/products/{product}/edit', SellerProductsEdit::class)->name('products.edit');

        // Orders
        Route::get('/orders', SellerOrdersIndex::class)->name('orders.index');
        Route::get('/orders/{order}', SellerOrdersShow::class)->name('orders.show');

        Route::put('/seller/profile/categories', [SellerProfileController::class, 'updateCategories'])->name('profile.categories.update');

        // Transactions
        Route::get('/transactions', SellerTransactionsIndex::class)->name('transactions.index');

    });

});
