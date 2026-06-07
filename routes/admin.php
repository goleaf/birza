<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Backend\AdminLandingController;
use App\Http\Controllers\Notifications\MarkAllNotificationsReadController;
use App\Http\Controllers\Notifications\MarkNotificationReadController;
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
use App\Livewire\Backend\Messages\Index as MessagesIndex;
use App\Livewire\Backend\Messages\Show as MessagesShow;
use App\Livewire\Backend\Notifications\Index as BackendNotificationsIndex;
use App\Livewire\Backend\Orders\Index as OrdersIndex;
use App\Livewire\Backend\Orders\Show as OrdersShow;
use App\Livewire\Backend\ProductBundles\Index as ProductBundlesIndex;
use App\Livewire\Backend\ProductBundles\Show as ProductBundlesShow;
use App\Livewire\Backend\ProductQuestions\Index as ProductQuestionsIndex;
use App\Livewire\Backend\ProductReports\Index as ProductReportsIndex;
use App\Livewire\Backend\ProductReports\Show as ProductReportsShow;
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

Route::prefix('admin')->as('admin.')->group(function () {
    Route::get('/', AdminLandingController::class);

    // Auth Routes
    Route::middleware('guest:admin')->group(function () {
        Route::livewire('login', AdminLogin::class)->name('login');
    });

    // Protected Routes
    Route::middleware(['auth:admin', 'active.account:admin', 'admin.access'])->group(function () {
        Route::post('logout', LogoutController::class)
            ->defaults('guard', 'admin')
            ->defaults('redirectRoute', 'admin.login')
            ->name('logout');

        // Dashboard
        Route::livewire('/dashboard', AdminDashboard::class)->name('dashboard');

        // Admin Profile Routes
        Route::livewire('/profile', AdminProfile::class)->name('profile');

        // Buyer Credit History Routes
        Route::livewire('/buyers/{buyer}/credit-history', BuyerCreditHistory::class)->name('buyers.credit_history');

        // Countries
        Route::livewire('countries', CountriesIndex::class)->name('countries.index');
        Route::livewire('countries/create', CountryForm::class)->name('countries.create');
        Route::livewire('countries/{country}/edit', CountryForm::class)->name('countries.edit');

        // Categories
        Route::livewire('categories', CategoriesIndex::class)->name('categories.index');
        Route::livewire('categories/create', CategoryForm::class)->name('categories.create');
        Route::livewire('categories/{category}/edit', CategoryForm::class)->name('categories.edit');

        // Products
        Route::livewire('products', ProductsIndex::class)->name('products.index');
        Route::livewire('products/create', ProductCreate::class)->name('products.create');
        Route::livewire('products/{product}', ProductShow::class)->name('products.show');
        Route::livewire('products/{product}/edit', ProductEdit::class)->name('products.edit');

        // Product Bundles
        Route::livewire('bundles', ProductBundlesIndex::class)->name('bundles.index');
        Route::livewire('bundles/{productBundle}', ProductBundlesShow::class)->name('bundles.show');

        // Product Reports
        Route::livewire('reports', ProductReportsIndex::class)->name('reports.index');
        Route::livewire('reports/{productReport}', ProductReportsShow::class)->name('reports.show');
        Route::livewire('product-questions', ProductQuestionsIndex::class)->name('product-questions.index');

        // Attributes
        Route::livewire('attributes', AttributesIndex::class)->name('attributes.index');
        Route::livewire('attributes/create', AttributeForm::class)->name('attributes.create');
        Route::livewire('attributes/{attribute}/edit', AttributeForm::class)->name('attributes.edit');

        // Attribute Values
        Route::livewire('attributes/{attribute}/values', AttributeValuesIndex::class)->name('attributes.values.index');
        Route::livewire('attributes/{attribute}/values/create', AttributeValueForm::class)->name('attributes.values.create');
        Route::livewire('attributes/{attribute}/values/{value}/edit', AttributeValueForm::class)->name('attributes.values.edit');

        // Sellers
        Route::livewire('sellers', SellersIndex::class)->name('sellers.index');
        Route::livewire('sellers/create', SellerForm::class)->name('sellers.create');
        Route::livewire('sellers/{seller}', SellerShow::class)->name('sellers.show');
        Route::livewire('sellers/{seller}/edit', SellerForm::class)->name('sellers.edit');
        Route::livewire('sellers/{seller}/orders', SellerOrders::class)->name('sellers.orders');

        // Buyers
        Route::livewire('buyers', BuyersIndex::class)->name('buyers.index');
        Route::livewire('buyers/create', BuyerForm::class)->name('buyers.create');
        Route::livewire('buyers/{buyer}/edit', BuyerForm::class)->name('buyers.edit');
        Route::livewire('buyers/{buyer}/orders', BuyerOrders::class)->name('buyers.orders');
        Route::livewire('buyers/{buyer}/credit', BuyerCredit::class)->name('buyers.credit');

        // Global Settings
        Route::livewire('settings', SettingsIndex::class)->name('settings.index');

        // Audit Trail
        Route::livewire('audit', AuditLogsIndex::class)->name('audit.index');
        Route::livewire('audit/{auditLog}', AuditLogsShow::class)->name('audit.show');

        // Messages
        Route::livewire('messages', MessagesIndex::class)->name('messages.index');
        Route::livewire('messages/{conversation}', MessagesShow::class)->name('messages.show');

        // Orders
        Route::livewire('orders', OrdersIndex::class)->name('orders.index');
        Route::livewire('orders/{order}', OrdersShow::class)->name('orders.show');

        // Notifications
        Route::livewire('notifications', BackendNotificationsIndex::class)->name('notifications.index');
        Route::post('notifications/read-all', MarkAllNotificationsReadController::class)
            ->defaults('guard', 'admin')
            ->name('notifications.read_all');
        Route::post('notifications/{notification}/read', MarkNotificationReadController::class)
            ->defaults('guard', 'admin')
            ->name('notifications.read');
    });
});
