<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Backend\AdminLandingController;
use App\Livewire\Backend\Admin\Profile as AdminProfile;
use App\Livewire\Backend\Attributes\Form as AttributeForm;
use App\Livewire\Backend\Attributes\Index as AttributesIndex;
use App\Livewire\Backend\Attributes\Values\Form as AttributeValueForm;
use App\Livewire\Backend\Attributes\Values\Index as AttributeValuesIndex;
use App\Livewire\Backend\AuditLogs\Index as AuditLogsIndex;
use App\Livewire\Backend\AuditLogs\Show as AuditLogsShow;
use App\Livewire\Backend\Auth\Login as AdminLogin;
use App\Livewire\Backend\Buyers\Credit as BuyerCredit;
use App\Livewire\Backend\Buyers\CreditHistory as BuyerCreditHistory;
use App\Livewire\Backend\Buyers\Form as BuyerForm;
use App\Livewire\Backend\Buyers\Index as BuyersIndex;
use App\Livewire\Backend\Buyers\Orders as BuyerOrders;
use App\Livewire\Backend\Categories\Form as CategoryForm;
use App\Livewire\Backend\Categories\Index as CategoriesIndex;
use App\Livewire\Backend\Countries\Form as CountryForm;
use App\Livewire\Backend\Countries\Index as CountriesIndex;
use App\Livewire\Backend\Dashboard as AdminDashboard;
use App\Livewire\Backend\Orders\Index as OrdersIndex;
use App\Livewire\Backend\Orders\Show as OrdersShow;
use App\Livewire\Backend\Products\Create as ProductCreate;
use App\Livewire\Backend\Products\Edit as ProductEdit;
use App\Livewire\Backend\Products\Index as ProductsIndex;
use App\Livewire\Backend\Products\Show as ProductShow;
use App\Livewire\Backend\Sellers\Form as SellerForm;
use App\Livewire\Backend\Sellers\Index as SellersIndex;
use App\Livewire\Backend\Sellers\Orders as SellerOrders;
use App\Livewire\Backend\Sellers\Show as SellerShow;
use App\Livewire\Backend\Settings\Index as SettingsIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/', AdminLandingController::class);

    // Auth Routes
    Route::middleware('guest:admin')->group(function () {
        Route::livewire('login', AdminLogin::class)->name('backend.login');
    });

    // Protected Routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', LogoutController::class)
            ->defaults('guard', 'admin')
            ->defaults('redirectRoute', 'backend.login')
            ->name('backend.logout');

        // Dashboard
        Route::livewire('/dashboard', AdminDashboard::class)->name('backend.dashboard');

        // Admin Profile Routes
        Route::livewire('/profile', AdminProfile::class)->name('backend.admin.profile');

        // Buyer Credit History Routes
        Route::livewire('/buyers/{buyer}/credit-history', BuyerCreditHistory::class)->name('backend.buyers.credit_history');

        // Countries
        Route::livewire('countries', CountriesIndex::class)->name('backend.countries.index');
        Route::livewire('countries/create', CountryForm::class)->name('backend.countries.create');
        Route::livewire('countries/{country}/edit', CountryForm::class)->name('backend.countries.edit');

        // Categories
        Route::livewire('categories', CategoriesIndex::class)->name('backend.categories.index');
        Route::livewire('categories/create', CategoryForm::class)->name('backend.categories.create');
        Route::livewire('categories/{category}/edit', CategoryForm::class)->name('backend.categories.edit');

        // Products
        Route::livewire('products', ProductsIndex::class)->name('backend.products.index');
        Route::livewire('products/create', ProductCreate::class)->name('backend.products.create');
        Route::livewire('products/{product}', ProductShow::class)->name('backend.products.show');
        Route::livewire('products/{product}/edit', ProductEdit::class)->name('backend.products.edit');

        // Attributes
        Route::livewire('attributes', AttributesIndex::class)->name('backend.attributes.index');
        Route::livewire('attributes/create', AttributeForm::class)->name('backend.attributes.create');
        Route::livewire('attributes/{attribute}/edit', AttributeForm::class)->name('backend.attributes.edit');

        // Attribute Values
        Route::livewire('attributes/{attribute}/values', AttributeValuesIndex::class)->name('backend.attributes.values.index');
        Route::livewire('attributes/{attribute}/values/create', AttributeValueForm::class)->name('backend.attributes.values.create');
        Route::livewire('attributes/{attribute}/values/{value}/edit', AttributeValueForm::class)->name('backend.attributes.values.edit');

        // Sellers
        Route::livewire('sellers', SellersIndex::class)->name('backend.sellers.index');
        Route::livewire('sellers/create', SellerForm::class)->name('backend.sellers.create');
        Route::livewire('sellers/{seller}', SellerShow::class)->name('backend.sellers.show');
        Route::livewire('sellers/{seller}/edit', SellerForm::class)->name('backend.sellers.edit');
        Route::livewire('sellers/{seller}/orders', SellerOrders::class)->name('backend.sellers.orders');

        // Buyers
        Route::livewire('buyers', BuyersIndex::class)->name('backend.buyers.index');
        Route::livewire('buyers/create', BuyerForm::class)->name('backend.buyers.create');
        Route::livewire('buyers/{buyer}/edit', BuyerForm::class)->name('backend.buyers.edit');
        Route::livewire('buyers/{buyer}/orders', BuyerOrders::class)->name('backend.buyers.orders');
        Route::livewire('buyers/{buyer}/credit', BuyerCredit::class)->name('backend.buyers.credit');

        // Global Settings
        Route::livewire('settings', SettingsIndex::class)->name('backend.settings.index');

        // Audit Trail
        Route::livewire('audit', AuditLogsIndex::class)->name('backend.audit.index');
        Route::livewire('audit/{auditLog}', AuditLogsShow::class)->name('backend.audit.show');

        // Orders
        Route::livewire('orders', OrdersIndex::class)->name('backend.orders.index');
        Route::livewire('orders/{order}', OrdersShow::class)->name('backend.orders.show');
    });
});
