<?php

namespace Tests\Feature\Seeders;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReport;
use App\Models\Review;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Models\Wishlist;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoScenarioSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_idempotent_demo_marketplace_data(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        $initialCounts = $this->counts();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($initialCounts, $this->counts());

        $this->assertDatabaseHas('users_admins', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('users_admins', ['email' => 'admin@admin.com']);
        $this->assertDatabaseHas('users_buyers', ['email' => 'buyer@example.com', 'is_active' => true]);
        $this->assertDatabaseHas('users_buyers', ['email' => 'blocked-buyer@example.com', 'is_active' => false]);
        $this->assertDatabaseHas('users_buyers', ['email' => 'unverified-buyer@example.com', 'is_verified' => false]);
        $this->assertDatabaseHas('users_sellers', ['email' => 'seller@example.com', 'is_active' => true]);
        $this->assertDatabaseHas('users_sellers', ['email' => 'blocked-seller@example.com', 'is_active' => false]);
        $this->assertDatabaseHas('users_sellers', ['email' => 'unverified-seller@example.com', 'is_verified' => false]);

        $this->assertGreaterThan(0, Category::query()->count());
        $this->assertDatabaseHas('categories', ['is_active' => false]);
        $this->assertGreaterThanOrEqual(25, Product::query()->where('name', 'like', 'Demo Pagination Product%')->count());
        $this->assertDatabaseHas('products', ['name' => 'Demo Product Without Image', 'product_image' => '']);
        $this->assertDatabaseHas('products', ['name' => 'Demo Out Of Stock Milk', 'stock' => 0]);
        $this->assertDatabaseHas('products', ['name' => 'Demo Low Stock Yogurt', 'stock' => 2]);
        $this->assertDatabaseHas('products', ['name' => 'Demo Inactive Honey', 'is_active' => false]);
        $this->assertNotNull(Product::withTrashed()->where('name', 'Demo Soft Deleted Product')->first()?->deleted_at);

        foreach (OrderStatus::values() as $status) {
            $this->assertDatabaseHas('orders', ['status' => $status]);
        }

        foreach (Order::query()->pluck('status')->all() as $status) {
            $this->assertContains($status instanceof OrderStatus ? $status->value : $status, OrderStatus::values());
        }

        foreach (Order::query()->pluck('payment_status')->all() as $paymentStatus) {
            $this->assertContains(
                $paymentStatus instanceof OrderPaymentStatus ? $paymentStatus->value : $paymentStatus,
                OrderPaymentStatus::values(),
            );
        }

        $this->assertGreaterThan(0, Order::query()->whereHas('items')->count());
        $this->assertDatabaseHas('order_items', ['product_title_snapshot' => 'Demo Soft Deleted Product']);

        $buyer = Buyer::query()->where('email', 'buyer@example.com')->firstOrFail();
        $this->assertTrue(Cart::query()->where('user_id', $buyer->id)->whereHas('items')->exists());
        $this->assertTrue(Cart::query()->where('guest_token', 'demo-guest-cart')->whereHas('items')->exists());

        $this->assertGreaterThan(0, Review::query()->count());
        $this->assertGreaterThan(0, Wishlist::query()->count());
        $this->assertGreaterThan(0, ProductReport::query()->count());
        $this->assertGreaterThan(0, Notification::query()->count());
        $this->assertGreaterThanOrEqual(
            12,
            $buyer->notifications()
                ->where('data->source', 'demo_seeder')
                ->count(),
        );
        $this->assertTrue(Notification::query()->where('type', 'marketplace.stock.low')->exists());
        $this->assertTrue(Notification::query()->where('type', 'marketplace.product.moderation_required')->exists());
        $this->assertTrue(Notification::query()->where('type', 'marketplace.product_report.created')->exists());
        $this->assertGreaterThan(0, ProductImage::query()->count());

        $image = ProductImage::query()
            ->where('mime_type', 'image/svg+xml')
            ->where('original_name', 'like', 'demo-%')
            ->firstOrFail();

        Storage::disk($image->disk)->assertExists($image->path);

        $emptySeller = Seller::query()->where('email', 'seller-empty@example.com')->firstOrFail();
        $emptyBuyer = Buyer::query()->where('email', 'demo-empty-buyer@example.com')->firstOrFail();

        $this->assertFalse($emptySeller->products()->exists());
        $this->assertFalse($emptyBuyer->orders()->exists());
    }

    /**
     * @return array<string, int>
     */
    private function counts(): array
    {
        return [
            'admins' => Admin::query()->count(),
            'buyers' => Buyer::query()->count(),
            'sellers' => Seller::query()->count(),
            'categories' => Category::query()->count(),
            'products' => Product::withTrashed()->count(),
            'orders' => Order::withTrashed()->count(),
            'carts' => Cart::query()->count(),
            'product_images' => ProductImage::query()->count(),
            'reviews' => Review::withTrashed()->count(),
            'wishlists' => Wishlist::query()->count(),
            'product_reports' => ProductReport::withTrashed()->count(),
            'notifications' => Notification::query()->count(),
        ];
    }
}
