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
use App\Livewire\Frontend\Notifications\Index as FrontendNotificationsIndex;
use App\Livewire\Frontend\Seller\Dashboard as SellerDashboard;
use App\Livewire\Frontend\Seller\Discounts\Index as SellerDiscountsIndex;
use App\Livewire\Frontend\Seller\Messages\Index as SellerMessagesIndex;
use App\Livewire\Frontend\Seller\Messages\Show as SellerMessagesShow;
use App\Livewire\Frontend\Seller\Orders\Index as SellerOrdersIndex;
use App\Livewire\Frontend\Seller\Orders\Show as SellerOrdersShow;
use App\Livewire\Frontend\Seller\ProductBundles\Form as SellerProductBundlesForm;
use App\Livewire\Frontend\Seller\ProductBundles\Index as SellerProductBundlesIndex;
use App\Livewire\Frontend\Seller\ProductQuestions\Index as SellerProductQuestionsIndex;
use App\Livewire\Frontend\Seller\Products\Create as SellerProductsCreate;
use App\Livewire\Frontend\Seller\Products\Edit as SellerProductsEdit;
use App\Livewire\Frontend\Seller\Products\Index as SellerProductsIndex;
use App\Livewire\Frontend\Seller\Profile\Edit as SellerProfileEdit;
use App\Livewire\Frontend\Seller\PromoCodes\Index as SellerPromoCodesIndex;
use App\Livewire\Frontend\Seller\Transactions\Index as SellerTransactionsIndex;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'seller', 'as' => 'seller.'], function () {
    // Guest routes
    Route::middleware('guest:seller')->group(function () {
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
    Route::middleware(['auth:seller', 'active.account:seller', 'verified.account:seller', 'seller.access'])->group(function () {
        Route::livewire('/dashboard', SellerDashboard::class)->name('dashboard');
        Route::livewire('/profile', SellerProfileEdit::class)->name('profile.edit');
        Route::post('/logout', LogoutController::class)
            ->defaults('guard', 'seller')
            ->defaults('redirectRoute', 'home')
            ->defaults('flashMessage', 'messages_logout_success')
            ->name('logout');

        // Seller specific routes
        Route::livewire('/products', SellerProductsIndex::class)->name('products.index');
        Route::livewire('/products/create/{categoryId}', SellerProductsCreate::class)->name('products.create');
        Route::livewire('/products/{product}/edit', SellerProductsEdit::class)->name('products.edit');
        Route::livewire('/bundles', SellerProductBundlesIndex::class)->name('bundles.index');
        Route::livewire('/bundles/create', SellerProductBundlesForm::class)->name('bundles.create');
        Route::livewire('/bundles/{productBundle}/edit', SellerProductBundlesForm::class)->name('bundles.edit');
        Route::livewire('/discounts', SellerDiscountsIndex::class)->name('discounts.index');
        Route::livewire('/promo-codes', SellerPromoCodesIndex::class)->name('promo-codes.index');
        Route::livewire('/product-questions', SellerProductQuestionsIndex::class)->name('product-questions.index');

        // Orders
        Route::livewire('/orders', SellerOrdersIndex::class)->name('orders.index');
        Route::livewire('/orders/{order}', SellerOrdersShow::class)->name('orders.show');
        Route::livewire('/messages', SellerMessagesIndex::class)->name('messages.index');
        Route::livewire('/messages/{conversation}', SellerMessagesShow::class)->name('messages.show');

        // Transactions
        Route::livewire('/transactions', SellerTransactionsIndex::class)->name('transactions.index');

        Route::livewire('/notifications', FrontendNotificationsIndex::class)->name('notifications.index');
        Route::post('/notifications/read-all', MarkAllNotificationsReadController::class)
            ->defaults('guard', 'seller')
            ->name('notifications.read_all');
        Route::post('/notifications/{notification}/read', MarkNotificationReadController::class)
            ->defaults('guard', 'seller')
            ->name('notifications.read');
    });
});
