<?php

use App\Http\Controllers\Backend\Auth\LoginController;
use App\Http\Controllers\Backend\BuyerController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\AttributeController;
use App\Http\Controllers\Backend\AttributeValueController;
use App\Http\Controllers\Backend\CountryController;
use App\Http\Controllers\Backend\GlobalSettingsController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\SellerController;
use App\Http\Controllers\Backend\AdminProfileController;
use App\Http\Controllers\Backend\BuyerCreditHistoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::prefix('admin')->group(function () {
    // Redirect root /admin to dashboard if logged in, otherwise to login
    Route::get('/', function () {
        return Auth::guard('admin')->check() 
            ? redirect()->route('backend.dashboard')
            : redirect()->route('backend.login');
    });

    // Auth Routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('backend.login');
        Route::post('login', [LoginController::class, 'login']);
    });

    // Protected Routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('backend.logout');
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('backend.dashboard');

        // Admin Profile Routes
        Route::get('/profile', [AdminProfileController::class, 'index'])->name('backend.admin.profile');
        Route::patch('/profile', [AdminProfileController::class, 'updateProfile'])->name('backend.admin.profile.update');
        Route::patch('/profile/password', [AdminProfileController::class, 'changePassword'])->name('backend.admin.password.update');

        // Buyer Credit History Routes
        Route::get('/buyers/{buyer}/credit-history', [BuyerCreditHistoryController::class, 'index'])->name('backend.buyers.credit_history');
        Route::post('/buyers/{buyer}/add-credit', [BuyerCreditHistoryController::class, 'addCredit'])->name('backend.buyers.add-credit');
        Route::post('/buyers/{buyer}/debit-credit', [BuyerCreditHistoryController::class, 'debitCredit'])->name('backend.buyers.debit-credit');
        Route::get('/buyers/credit-history/export', [BuyerCreditHistoryController::class, 'export'])->name('backend.buyers.credit_history.export');
        Route::get('credit-attachments/{attachment}', [BuyerCreditHistoryController::class, 'downloadAttachment'])->name('backend.credit.attachments.download');

        // Countries
        Route::resource('countries', CountryController::class)->names([
            'index' => 'backend.countries.index',
            'create' => 'backend.countries.create',
            'store' => 'backend.countries.store',
            'edit' => 'backend.countries.edit',
            'update' => 'backend.countries.update',
            'destroy' => 'backend.countries.destroy',
        ]);

        // Categories
        Route::resource('categories', CategoryController::class)->names([
            'index' => 'backend.categories.index',
            'create' => 'backend.categories.create',
            'store' => 'backend.categories.store',
            'edit' => 'backend.categories.edit',
            'update' => 'backend.categories.update',
            'destroy' => 'backend.categories.destroy',
        ]);

        // Products
        Route::resource('products', ProductController::class)->names([
            'index' => 'backend.products.index',
            'create' => 'backend.products.create',
            'store' => 'backend.products.store',
            'show' => 'backend.products.show',
            'edit' => 'backend.products.edit',
            'update' => 'backend.products.update',
            'destroy' => 'backend.products.destroy',
        ]);
        Route::post('products/{product}/restore', [ProductController::class, 'restore'])->name('backend.products.restore');
        Route::delete('products/{product}/force-delete', [ProductController::class, 'forceDelete'])->name('backend.products.force-delete');

        // Attributes
        Route::resource('attributes', AttributeController::class)->names([
            'index' => 'backend.attributes.index',
            'create' => 'backend.attributes.create',
            'store' => 'backend.attributes.store',
            'edit' => 'backend.attributes.edit',
            'update' => 'backend.attributes.update',
            'destroy' => 'backend.attributes.destroy',
        ]);

        // Attribute Values
        Route::get('attributes/{attribute}/values/create', [AttributeValueController::class, 'create'])->name('backend.attributes.values.create');
        Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('backend.attributes.values.store');
        Route::get('attributes/{attribute}/values/{value}/edit', [AttributeValueController::class, 'edit'])->name('backend.attributes.values.edit');
        Route::put('attributes/{attribute}/values/{value}', [AttributeValueController::class, 'update'])->name('backend.attributes.values.update');
        Route::delete('attributes/{attribute}/values/{value}', [AttributeValueController::class, 'destroy'])->name('backend.attributes.values.destroy');

        // Sellers
        Route::resource('sellers', SellerController::class)->names([
            'index' => 'backend.sellers.index',
            'create' => 'backend.sellers.create',
            'store' => 'backend.sellers.store',
            'show' => 'backend.sellers.show',
            'edit' => 'backend.sellers.edit',
            'update' => 'backend.sellers.update',
            'destroy' => 'backend.sellers.destroy',
        ]);
        Route::get('sellers/{id}/orders', [SellerController::class, 'orders'])->name('backend.sellers.orders');

        // Buyers
        Route::resource('buyers', BuyerController::class)->names([
            'index' => 'backend.buyers.index',
            'create' => 'backend.buyers.create',
            'store' => 'backend.buyers.store',
            'edit' => 'backend.buyers.edit',
            'update' => 'backend.buyers.update',
            'destroy' => 'backend.buyers.destroy',
        ]);
        Route::get('buyers/{buyer}/orders', [BuyerController::class, 'orders'])->name('backend.buyers.orders');
        Route::get('buyers/{buyer}/credit', [BuyerController::class, 'showCredit'])->name('backend.buyers.credit');
        Route::patch('buyers/{buyer}/credit', [BuyerController::class, 'updateCredit'])->name('backend.buyers.credit.update');

        // Global Settings
        Route::get('settings', [GlobalSettingsController::class, 'index'])->name('backend.settings.index');
        
        // Orders
        Route::resource('orders', OrderController::class)->names([
            'index' => 'backend.orders.index',
            'show' => 'backend.orders.show',
            'edit' => 'backend.orders.edit',
            'update' => 'backend.orders.update',
        ])->except(['create', 'store', 'destroy']);

        // Settings
        Route::get('settings/edit', [GlobalSettingsController::class, 'edit'])->name('backend.settings.edit');
        Route::patch('settings', [GlobalSettingsController::class, 'update'])->name('backend.settings.update');
    });
});

