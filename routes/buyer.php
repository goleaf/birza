<?php

use App\Http\Controllers\Frontend\Auth\AuthController;
use App\Http\Controllers\Frontend\Auth\BuyerAuthController;
use App\Http\Controllers\Frontend\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Frontend\Buyer\ProductController as BuyerProductController;
use App\Http\Controllers\Frontend\Buyer\ProfileController as BuyerProfileController;
use App\Http\Controllers\Frontend\Buyer\CartController as BuyerCartController;
use App\Http\Controllers\Frontend\Buyer\OrderController as BuyerOrderController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'buyer', 'as' => 'buyer.'], function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [BuyerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [BuyerAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [BuyerAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [BuyerAuthController::class, 'register'])->name('register.submit');
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
        Route::get('/dashboard', [BuyerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [BuyerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [BuyerProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [BuyerProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/products', [BuyerProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [BuyerProductController::class, 'show'])->name('products.show');

        Route::get('/cart', [BuyerCartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{product}', [BuyerCartController::class, 'addToCart'])->name('cart.add');
        Route::delete('/cart/remove/{itemHash}', [BuyerCartController::class, 'removeFromCart'])->name('cart.remove');
        Route::patch('/cart/update-quantity/{itemHash}', [BuyerCartController::class, 'updateQuantity'])->name('cart.update-quantity');
        Route::post('/cart/checkout', [BuyerCartController::class, 'checkout'])->name('cart.checkout');

        Route::get('/orders', [BuyerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [BuyerOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/cancel', [BuyerOrderController::class, 'cancel'])->name('orders.cancel');

    });
});
