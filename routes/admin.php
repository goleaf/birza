<?php

use App\Http\Controllers\Backend\BuyerCreditHistoryController;
use App\Livewire\Backend\Admin\Profile as AdminProfile;
use App\Livewire\Backend\Attributes\Form as AttributeForm;
use App\Livewire\Backend\Attributes\Index as AttributesIndex;
use App\Livewire\Backend\Attributes\Values\Form as AttributeValueForm;
use App\Livewire\Backend\Attributes\Values\Index as AttributeValuesIndex;
use App\Livewire\Backend\Buyers\Credit as BuyerCredit;
use App\Livewire\Backend\Buyers\CreditHistory as BuyerCreditHistory;
use App\Livewire\Backend\Buyers\Form as BuyerForm;
use App\Livewire\Backend\Buyers\Index as BuyersIndex;
use App\Livewire\Backend\Buyers\Orders as BuyerOrders;
use App\Livewire\Backend\Dashboard as AdminDashboard;
use App\Livewire\Backend\Categories\Form as CategoryForm;
use App\Livewire\Backend\Categories\Index as CategoriesIndex;
use App\Livewire\Backend\Countries\Form as CountryForm;
use App\Livewire\Backend\Countries\Index as CountriesIndex;
use App\Livewire\Backend\Orders\Index as OrdersIndex;
use App\Livewire\Backend\Orders\Show as OrdersShow;
use App\Livewire\Backend\Products\Edit as ProductEdit;
use App\Livewire\Backend\Products\Index as ProductsIndex;
use App\Livewire\Backend\Products\Show as ProductShow;
use App\Livewire\Backend\Auth\Login as AdminLogin;
use App\Livewire\Backend\Sellers\Form as SellerForm;
use App\Livewire\Backend\Sellers\Index as SellersIndex;
use App\Livewire\Backend\Sellers\Orders as SellerOrders;
use App\Livewire\Backend\Sellers\Show as SellerShow;
use App\Livewire\Backend\Settings\Index as SettingsIndex;
use Illuminate\Http\Request;
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
        Route::get('login', AdminLogin::class)->name('backend.login');
    });

    // Protected Routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', function (Request $request) {
            Auth::guard('admin')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('backend.login');
        })->name('backend.logout');

        // Dashboard
        Route::get('/dashboard', AdminDashboard::class)->name('backend.dashboard');

        // Admin Profile Routes
        Route::get('/profile', AdminProfile::class)->name('backend.admin.profile');

        // Buyer Credit History Routes
        Route::get('/buyers/{buyer}/credit-history', BuyerCreditHistory::class)->name('backend.buyers.credit_history');
        Route::get('/buyers/credit-history/export', [BuyerCreditHistoryController::class, 'export'])->name('backend.buyers.credit_history.export');
        Route::get('credit-attachments/{attachment}', [BuyerCreditHistoryController::class, 'downloadAttachment'])->name('backend.credit.attachments.download');

        // Countries
        Route::get('countries', CountriesIndex::class)->name('backend.countries.index');
        Route::get('countries/create', CountryForm::class)->name('backend.countries.create');
        Route::get('countries/{country}/edit', CountryForm::class)->name('backend.countries.edit');

        // Categories
        Route::get('categories', CategoriesIndex::class)->name('backend.categories.index');
        Route::get('categories/create', CategoryForm::class)->name('backend.categories.create');
        Route::get('categories/{category}/edit', CategoryForm::class)->name('backend.categories.edit');

        // Products
        Route::get('products', ProductsIndex::class)->name('backend.products.index');
        Route::get('products/{product}', ProductShow::class)->name('backend.products.show');
        Route::get('products/{product}/edit', ProductEdit::class)->name('backend.products.edit');

        // Attributes
        Route::get('attributes', AttributesIndex::class)->name('backend.attributes.index');
        Route::get('attributes/create', AttributeForm::class)->name('backend.attributes.create');
        Route::get('attributes/{attribute}/edit', AttributeForm::class)->name('backend.attributes.edit');

        // Attribute Values
        Route::get('attributes/{attribute}/values', AttributeValuesIndex::class)->name('backend.attributes.values.index');
        Route::get('attributes/{attribute}/values/create', AttributeValueForm::class)->name('backend.attributes.values.create');
        Route::get('attributes/{attribute}/values/{value}/edit', AttributeValueForm::class)->name('backend.attributes.values.edit');

        // Sellers
        Route::get('sellers', SellersIndex::class)->name('backend.sellers.index');
        Route::get('sellers/create', SellerForm::class)->name('backend.sellers.create');
        Route::get('sellers/{seller}', SellerShow::class)->name('backend.sellers.show');
        Route::get('sellers/{seller}/edit', SellerForm::class)->name('backend.sellers.edit');
        Route::get('sellers/{seller}/orders', SellerOrders::class)->name('backend.sellers.orders');

        // Buyers
        Route::get('buyers', BuyersIndex::class)->name('backend.buyers.index');
        Route::get('buyers/create', BuyerForm::class)->name('backend.buyers.create');
        Route::get('buyers/{buyer}/edit', BuyerForm::class)->name('backend.buyers.edit');
        Route::get('buyers/{buyer}/orders', BuyerOrders::class)->name('backend.buyers.orders');
        Route::get('buyers/{buyer}/credit', BuyerCredit::class)->name('backend.buyers.credit');

        // Global Settings
        Route::get('settings', SettingsIndex::class)->name('backend.settings.index');

        // Orders
        Route::get('orders', OrdersIndex::class)->name('backend.orders.index');
        Route::get('orders/{order}', OrdersShow::class)->name('backend.orders.show');
    });
});

