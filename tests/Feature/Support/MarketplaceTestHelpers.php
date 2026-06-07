<?php

namespace Tests\Feature\Support;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Country;
use App\Models\GlobalSettings;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

trait MarketplaceTestHelpers
{
    protected function createAdmin(array $attributes = []): Admin
    {
        return Admin::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
        ], $attributes));
    }

    protected function createBuyer(array $attributes = []): Buyer
    {
        return Buyer::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_verified' => true,
        ], $attributes));
    }

    protected function createSeller(array $attributes = []): Seller
    {
        return Seller::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_verified' => true,
        ], $attributes));
    }

    protected function createProduct(array $attributes = []): Product
    {
        $parent = Category::factory()->create([
            'category_name' => ['en' => 'Food', 'lt' => 'Maistas'],
        ]);

        $category = Category::factory()->create([
            'parent_category_id' => $parent->id,
            'category_name' => ['en' => 'Dairy', 'lt' => 'Pienas'],
        ]);

        $country = $this->createLithuanianCountry();

        return Product::factory()->active()->create(array_merge([
            'category_id' => $category->id,
            'country_of_origin' => $country->id,
            'seller_id' => $this->createSeller()->id,
            'price' => 10.00,
            'min_order_count' => 1,
            'stock' => 10,
            'unit' => 'kg',
            'product_image' => '',
            'product_additional_image' => '',
        ], $attributes));
    }

    protected function createOrderWithItem(
        ?Buyer $buyer = null,
        ?Seller $seller = null,
        ?Product $product = null,
        array $orderAttributes = [],
        array $itemAttributes = [],
    ): Order {
        $buyer ??= $this->createBuyer();
        $seller ??= $this->createSeller();
        $product ??= $this->createProduct(['seller_id' => $seller->id]);

        $order = Order::factory()->for($buyer, 'buyer')->create(array_merge([
            'payment_status' => OrderPaymentStatus::Pending,
            'status' => OrderStatus::Pending,
            'order_total' => 20.00,
        ], $orderAttributes));

        OrderItem::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'total_price' => 20.00,
        ], $itemAttributes));

        return $order;
    }

    protected function actingAsBuyer(?Buyer $buyer = null): Authenticatable
    {
        $buyer ??= $this->createBuyer();

        $this->actingAs($buyer, 'buyer');

        return $buyer;
    }

    protected function actingAsSeller(?Seller $seller = null): Authenticatable
    {
        $seller ??= $this->createSeller();

        $this->actingAs($seller, 'seller');

        return $seller;
    }

    protected function actingAsAdmin(?Admin $admin = null): Authenticatable
    {
        $admin ??= $this->createAdmin();

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    protected function createCartWithItem(
        ?Buyer $buyer = null,
        ?Product $product = null,
        int $quantity = 1,
        ?float $unitPrice = null,
        ?string $guestToken = null,
    ): CartItem {
        $buyer ??= $guestToken === null ? $this->createBuyer() : null;
        $product ??= $this->createProduct();

        $cart = Cart::factory()->create([
            'user_id' => $buyer?->id,
            'guest_token' => $buyer === null ? ($guestToken ?? fake()->uuid()) : null,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        return CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => $quantity,
            'unit_price' => $unitPrice ?? $product->price,
        ]);
    }

    protected function createPortalSettings(int $portalPrice = 0): GlobalSettings
    {
        return GlobalSettings::factory()->create([
            'portal_additional_price' => $portalPrice,
        ]);
    }

    protected function createLithuanianCountry(): Country
    {
        return Country::query()->firstOrCreate(
            ['alpha2' => 'LT'],
            [
                'region' => 'Europe',
                'is_active' => true,
                'country_name' => ['en' => 'Lithuania', 'lt' => 'Lietuva'],
                'description' => [
                    'en' => 'Lithuanian marketplace origin.',
                    'lt' => 'Lietuvos turgavietes kilmes salis.',
                ],
            ],
        );
    }
}
