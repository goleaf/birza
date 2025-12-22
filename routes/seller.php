<?php

use App\Http\Controllers\Frontend\Auth\AuthController;
use App\Http\Controllers\Frontend\Auth\SellerAuthController;
use App\Http\Controllers\Frontend\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Frontend\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Frontend\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Frontend\Seller\ProfileController as SellerProfileController;
use App\Http\Controllers\Frontend\Seller\TransactionController as SellerTransactionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'seller', 'as' => 'seller.'], function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SellerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SellerAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [SellerAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [SellerAuthController::class, 'register'])->name('register.submit');
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
        Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [SellerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [SellerProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [SellerProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/logout', [SellerAuthController::class, 'logout'])->name('logout');

        // Seller specific routes
        Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
        Route::get('/products/create/{categoryId}', [SellerProductController::class, 'create'])->name('products.create');
        Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [SellerProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [SellerProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/restore/{id}', [SellerProductController::class, 'restore'])->name('products.restore');

        // Orders
        Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');

        Route::put('/seller/profile/categories', [SellerProfileController::class, 'updateCategories'])->name('profile.categories.update');

        // Transactions
        Route::get('/transactions', [SellerTransactionController::class, 'index'])->name('transactions.index');

    });

});
