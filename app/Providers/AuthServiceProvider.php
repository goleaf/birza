<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\AdminAction;
use App\Models\Attribute as ProductAttribute;
use App\Models\AttributeValue;
use App\Models\AuditLog;
use App\Models\BuyerCreditHistory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Country;
use App\Models\CreditAttachment;
use App\Models\Discount;
use App\Models\GlobalSettings;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductImage;
use App\Models\ProductQuestion;
use App\Models\ProductReport;
use App\Models\ProductStockAlert;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Review;
use App\Models\SellerTransaction;
use App\Models\User;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Models\Wishlist;
use App\Policies\AddressPolicy;
use App\Policies\AdminActionPolicy;
use App\Policies\AdminPolicy;
use App\Policies\AttributePolicy;
use App\Policies\AttributeValuePolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\BuyerCreditHistoryPolicy;
use App\Policies\BuyerPolicy;
use App\Policies\CartItemPolicy;
use App\Policies\CartPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CountryPolicy;
use App\Policies\CreditAttachmentPolicy;
use App\Policies\DiscountPolicy;
use App\Policies\GlobalSettingsPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderItemPolicy;
use App\Policies\OrderPolicy;
use App\Policies\OrderStatusHistoryPolicy;
use App\Policies\ProductBundlePolicy;
use App\Policies\ProductImagePolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductQuestionPolicy;
use App\Policies\ProductReportPolicy;
use App\Policies\ProductStockAlertPolicy;
use App\Policies\PromoCodePolicy;
use App\Policies\PromoCodeRedemptionPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\SellerPolicy;
use App\Policies\SellerTransactionPolicy;
use App\Policies\UserPolicy;
use App\Policies\WishlistPolicy;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Admin::class => AdminPolicy::class,
        AdminAction::class => AdminActionPolicy::class,
        Address::class => AddressPolicy::class,
        ProductAttribute::class => AttributePolicy::class,
        AttributeValue::class => AttributeValuePolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Buyer::class => BuyerPolicy::class,
        BuyerCreditHistory::class => BuyerCreditHistoryPolicy::class,
        Cart::class => CartPolicy::class,
        CartItem::class => CartItemPolicy::class,
        Category::class => CategoryPolicy::class,
        Country::class => CountryPolicy::class,
        CreditAttachment::class => CreditAttachmentPolicy::class,
        Discount::class => DiscountPolicy::class,
        GlobalSettings::class => GlobalSettingsPolicy::class,
        Notification::class => NotificationPolicy::class,
        Order::class => OrderPolicy::class,
        OrderItem::class => OrderItemPolicy::class,
        OrderStatusHistory::class => OrderStatusHistoryPolicy::class,
        PromoCode::class => PromoCodePolicy::class,
        PromoCodeRedemption::class => PromoCodeRedemptionPolicy::class,
        Product::class => ProductPolicy::class,
        ProductBundle::class => ProductBundlePolicy::class,
        ProductImage::class => ProductImagePolicy::class,
        ProductQuestion::class => ProductQuestionPolicy::class,
        ProductReport::class => ProductReportPolicy::class,
        ProductStockAlert::class => ProductStockAlertPolicy::class,
        Review::class => ReviewPolicy::class,
        Seller::class => SellerPolicy::class,
        SellerTransaction::class => SellerTransactionPolicy::class,
        User::class => UserPolicy::class,
        Wishlist::class => WishlistPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('accessAdminPanel', fn (Authenticatable $actor): bool => $actor instanceof Admin
            && (bool) $actor->is_active);

        Gate::define('accessSellerCabinet', fn (Authenticatable $actor): bool => $actor instanceof Seller
            && (bool) $actor->is_active
            && (bool) $actor->is_verified);

        Gate::define('accessBuyerCabinet', fn (Authenticatable $actor): bool => $actor instanceof Buyer
            && (bool) $actor->is_active
            && (bool) $actor->is_verified);

        Gate::define('manageSystemSettings', fn (Authenticatable $actor): bool => $actor instanceof Admin
            && (bool) $actor->is_active);

        Gate::define('viewAnalytics', fn (Authenticatable $actor): bool => $actor instanceof Admin
            && (bool) $actor->is_active);

        // Explicitly register the authentication providers
        $this->app['auth']->provider('sellers', function ($app, array $config) {
            return new EloquentUserProvider(
                $app['hash'],
                Seller::class
            );
        });

        $this->app['auth']->provider('buyers', function ($app, array $config) {
            return new EloquentUserProvider(
                $app['hash'],
                Buyer::class
            );
        });
    }
}
