<?php

namespace Tests\Feature\Marketplace;

use App\Actions\StockAlerts\CreateStockAlertAction;
use App\Actions\StockAlerts\DetectBackInStockAction;
use App\Actions\StockAlerts\NotifyBackInStockAction;
use App\Enums\ProductStockAlertStatus;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductShow;
use App\Livewire\Frontend\Buyer\StockAlerts\Index as BuyerStockAlertsIndex;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\ProductStockAlert;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Notifications\Marketplace\BackInStockNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ProductStockAlertFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_subscribe_to_out_of_stock_product(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'name' => 'Alert Milk',
            'stock' => 0,
        ]);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerProductShow::class, ['product' => $product])
            ->call('subscribeToStockAlert')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_stock_alerts', [
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'status' => ProductStockAlertStatus::Active->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'stock_alert.created',
            'actor_id' => $buyer->id,
            'actor_role' => 'buyer',
        ]);
    }

    public function test_buyer_cannot_subscribe_to_available_product(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 5,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(CreateStockAlertAction::class)->handle($product, $buyer);
        } finally {
            $this->assertDatabaseCount('product_stock_alerts', 0);
        }
    }

    public function test_buyer_cannot_subscribe_to_inactive_or_deleted_product(): void
    {
        $buyer = $this->createBuyer();
        $inactiveProduct = $this->createProduct([
            'is_active' => false,
            'stock' => 0,
        ]);
        $deletedProduct = $this->createProduct([
            'stock' => 0,
        ]);
        $deletedProduct->delete();

        foreach ([$inactiveProduct, $deletedProduct] as $product) {
            try {
                app(CreateStockAlertAction::class)->handle($product, $buyer);
                $this->fail('Inactive or deleted products must not accept stock alerts.');
            } catch (AuthorizationException) {
                $this->assertDatabaseMissing('product_stock_alerts', [
                    'product_id' => $product->id,
                    'buyer_id' => $buyer->id,
                ]);
            }
        }
    }

    public function test_duplicate_active_alert_is_not_created(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 0,
        ]);

        $first = app(CreateStockAlertAction::class)->handle($product, $buyer);
        $second = app(CreateStockAlertAction::class)->handle($product, $buyer);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('product_stock_alerts', 1);
    }

    public function test_buyer_can_cancel_own_stock_alert_from_list(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 0,
        ]);
        $alert = ProductStockAlert::factory()->active()->create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
        ]);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerStockAlertsIndex::class)
            ->call('cancelAlert', $alert->id)
            ->assertHasNoErrors();

        $this->assertSame(ProductStockAlertStatus::Cancelled, $alert->refresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'stock_alert.cancelled',
            'auditable_id' => $alert->id,
        ]);
    }

    public function test_buyer_cannot_cancel_another_buyers_stock_alert(): void
    {
        $owner = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 0,
        ]);
        $alert = ProductStockAlert::factory()->active()->create([
            'product_id' => $product->id,
            'buyer_id' => $owner->id,
        ]);

        $this->actingAs($otherBuyer, 'buyer');

        Livewire::test(BuyerStockAlertsIndex::class)
            ->call('cancelAlert', $alert->id)
            ->assertForbidden();

        $this->assertSame(ProductStockAlertStatus::Active, $alert->refresh()->status);
    }

    public function test_guest_cannot_create_stock_alerts_when_guest_alerts_are_not_supported(): void
    {
        $product = $this->createProduct([
            'stock' => 0,
        ]);

        $this->get(route('buyer.products.show', $product))
            ->assertOk()
            ->assertSee(__('stock_alerts.login_to_subscribe'))
            ->assertDontSee(__('stock_alerts.email'));

        $this->assertDatabaseCount('product_stock_alerts', 0);
    }

    public function test_notification_is_sent_when_product_becomes_available(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 0,
        ]);
        $alert = ProductStockAlert::factory()->active()->create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
        ]);

        $product->forceFill(['stock' => 6])->save();

        app(DetectBackInStockAction::class)->handle($product->refresh(), previousStock: 0, wasActive: true);

        Notification::assertSentTo($buyer, BackInStockNotification::class);
        $this->assertSame(ProductStockAlertStatus::Notified, $alert->refresh()->status);
        $this->assertNotNull($alert->notified_at);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'stock_alert.notification_sent',
            'auditable_id' => $alert->id,
        ]);
    }

    public function test_notification_is_not_sent_when_product_remains_unavailable(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 0,
        ]);
        $alert = ProductStockAlert::factory()->active()->create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
        ]);

        app(DetectBackInStockAction::class)->handle($product->refresh(), previousStock: 0, wasActive: true);

        Notification::assertNothingSent();
        $this->assertSame(ProductStockAlertStatus::Active, $alert->refresh()->status);
    }

    public function test_notification_is_not_sent_for_inactive_product(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'is_active' => false,
            'stock' => 5,
        ]);
        $alert = ProductStockAlert::factory()->active()->create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
        ]);

        app(NotifyBackInStockAction::class)->handle($product->refresh());

        Notification::assertNothingSent();
        $this->assertSame(ProductStockAlertStatus::Active, $alert->refresh()->status);
    }

    public function test_duplicate_notifications_are_not_sent(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 5,
        ]);
        ProductStockAlert::factory()->active()->create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
        ]);

        app(NotifyBackInStockAction::class)->handle($product);
        app(NotifyBackInStockAction::class)->handle($product);

        Notification::assertSentTo($buyer, BackInStockNotification::class);
        Notification::assertSentTimes(BackInStockNotification::class, 1);
    }

    public function test_dashboard_and_alert_page_show_buyers_stock_alerts(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'name' => 'Dashboard Alert Cheese',
            'stock' => 0,
        ]);
        ProductStockAlert::factory()->active()->create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
        ]);

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee(__('stock_alerts.dashboard_title'))
            ->assertSee($product->name);

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.stock-alerts.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee(__('stock_alerts.status.active'));
    }

    public function test_seller_cannot_access_buyer_stock_alerts(): void
    {
        $seller = $this->createSeller();

        $this->actingAs($seller, 'seller')
            ->get(route('buyer.stock-alerts.index'))
            ->assertRedirect(route('home'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createBuyer(array $attributes = []): Buyer
    {
        return Buyer::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_verified' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createSeller(array $attributes = []): Seller
    {
        return Seller::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_verified' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Product
    {
        $parent = Category::factory()->create([
            'category_name' => ['en' => 'Food', 'lt' => 'Maistas'],
        ]);

        $category = Category::factory()->create([
            'parent_category_id' => $parent->id,
            'category_name' => ['en' => 'Dairy', 'lt' => 'Pienas'],
        ]);

        return Product::factory()->active()->create(array_merge([
            'category_id' => $category->id,
            'country_of_origin' => $this->createLithuanianCountry()->id,
            'seller_id' => $this->createSeller()->id,
            'price' => 10.00,
            'min_order_count' => 1,
            'stock' => 10,
            'unit' => 'kg',
            'product_image' => '',
            'product_additional_image' => '',
        ], $attributes));
    }

    private function createLithuanianCountry(): Country
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
