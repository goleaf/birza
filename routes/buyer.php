<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Notifications\MarkAllNotificationsReadController;
use App\Http\Controllers\Notifications\MarkNotificationReadController;
use App\Livewire\Frontend\Auth\ForgotPassword as FrontendForgotPassword;
use App\Livewire\Frontend\Auth\Login as FrontendLogin;
use App\Livewire\Frontend\Auth\Register as FrontendRegister;
use App\Livewire\Frontend\Auth\RegisterSuccess as FrontendRegisterSuccess;
use App\Livewire\Frontend\Auth\ResetPassword as FrontendResetPassword;
use App\Livewire\Frontend\Auth\VerificationNotice as FrontendVerificationNotice;
use App\Livewire\Frontend\Auth\VerifyEmail as FrontendVerifyEmail;
use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Livewire\Frontend\Buyer\Compare\Index as BuyerCompareIndex;
use App\Livewire\Frontend\Buyer\Dashboard as BuyerDashboard;
use App\Livewire\Frontend\Buyer\Messages\Index as BuyerMessagesIndex;
use App\Livewire\Frontend\Buyer\Messages\Show as BuyerMessagesShow;
use App\Livewire\Frontend\Buyer\Orders\Index as BuyerOrdersIndex;
use App\Livewire\Frontend\Buyer\Orders\Show as BuyerOrdersShow;
use App\Livewire\Frontend\Buyer\ProductBundles\Show as BuyerProductBundlesShow;
use App\Livewire\Frontend\Buyer\Products\Index as BuyerProductsIndex;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductsShow;
use App\Livewire\Frontend\Buyer\Profile\Edit as BuyerProfileEdit;
use App\Livewire\Frontend\Buyer\StockAlerts\Index as BuyerStockAlertsIndex;
use App\Livewire\Frontend\Buyer\Wishlists\Index as BuyerWishlistsIndex;
use App\Livewire\Frontend\Buyer\Wishlists\Show as BuyerWishlistsShow;
use App\Livewire\Frontend\Notifications\Index as FrontendNotificationsIndex;
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

    Route::livewire('/cart', BuyerCartIndex::class)->name('cart.index');
    Route::livewire('/compare', BuyerCompareIndex::class)->name('compare.index');
    Route::livewire('/products', BuyerProductsIndex::class)->name('products.index');
    Route::livewire('/products/{product}', BuyerProductsShow::class)->name('products.show');
    Route::livewire('/bundles/{productBundle}', BuyerProductBundlesShow::class)->name('bundles.show');

    // Authenticated routes
    Route::middleware(['auth:buyer', 'active.account:buyer', 'verified.account:buyer', 'buyer.access'])->group(function () {
        Route::livewire('/dashboard', BuyerDashboard::class)->name('dashboard');
        Route::livewire('/profile', BuyerProfileEdit::class)->name('profile.edit');
        Route::post('/logout', LogoutController::class)
            ->defaults('guard', 'buyer')
            ->defaults('redirectRoute', 'home')
            ->defaults('flashMessage', 'messages_logout_success')
            ->name('logout');

        Route::livewire('/orders', BuyerOrdersIndex::class)->name('orders.index');
        Route::livewire('/orders/{order}', BuyerOrdersShow::class)->name('orders.show');
        Route::livewire('/messages', BuyerMessagesIndex::class)->name('messages.index');
        Route::livewire('/messages/{conversation}', BuyerMessagesShow::class)->name('messages.show');

        Route::livewire('/stock-alerts', BuyerStockAlertsIndex::class)->name('stock-alerts.index');
        Route::livewire('/wishlists', BuyerWishlistsIndex::class)->name('wishlists.index');
        Route::livewire('/wishlists/{wishlist}', BuyerWishlistsShow::class)->name('wishlists.show');

        Route::livewire('/notifications', FrontendNotificationsIndex::class)->name('notifications.index');
        Route::post('/notifications/read-all', MarkAllNotificationsReadController::class)
            ->defaults('guard', 'buyer')
            ->name('notifications.read_all');
        Route::post('/notifications/{notification}/read', MarkNotificationReadController::class)
            ->defaults('guard', 'buyer')
            ->name('notifications.read');
    });
});
