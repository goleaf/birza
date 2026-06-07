<?php

namespace Tests\Feature\Notifications;

use App\Actions\Cart\CreateOrdersFromCartAction;
use App\Actions\Notifications\SendProductModerationNotificationAction;
use App\Actions\Notifications\SendStockThresholdNotificationAction;
use App\Actions\Orders\ChangeOrderStatusAction;
use App\Actions\ProductReports\CreateProductReportAction;
use App\Enums\OrderStatus;
use App\Enums\ProductReportReason;
use App\Models\Order;
use App\Notifications\Marketplace\LowStockNotification;
use App\Notifications\Marketplace\NewOrderForSellerNotification;
use App\Notifications\Marketplace\OrderCreatedNotification;
use App\Notifications\Marketplace\OrderStatusChangedNotification;
use App\Notifications\Marketplace\ProductApprovedNotification;
use App\Notifications\Marketplace\ProductModerationRequiredNotification;
use App\Notifications\Marketplace\ProductRejectedNotification;
use App\Notifications\Marketplace\ProductReportCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class MarketplaceNotificationSystemTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_checkout_notifies_buyer_and_seller(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer(['address' => 'Buyer Street 20']);
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'price' => 15.00,
            'stock' => 10,
            'min_order_count' => 1,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 2);

        $orders = app(CreateOrdersFromCartAction::class)->handle($cartItem->cart, $buyer, [
            'shipping_address' => 'Buyer Street 20',
            'payment_method' => 'bank_transfer',
        ]);

        $order = $orders->first();

        $this->assertInstanceOf(Order::class, $order);

        Notification::assertSentTo(
            $buyer,
            OrderCreatedNotification::class,
            fn (OrderCreatedNotification $notification, array $channels): bool => $notification->order->is($order)
                && $channels === ['database'],
        );

        Notification::assertSentTo(
            $seller,
            NewOrderForSellerNotification::class,
            fn (NewOrderForSellerNotification $notification, array $channels): bool => $notification->order->is($order)
                && in_array('database', $channels, true)
                && in_array('mail', $channels, true),
        );
    }

    public function test_buyer_cancellation_notifies_seller_but_not_actor(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $order = $this->createOrderWithItem($buyer, $seller);

        app(ChangeOrderStatusAction::class)->handle(
            order: $order,
            nextStatus: OrderStatus::Cancelled,
            actor: $buyer,
        );

        Notification::assertSentTo($seller, OrderStatusChangedNotification::class);
        Notification::assertNotSentTo($buyer, OrderStatusChangedNotification::class);
    }

    public function test_database_notification_stores_semantic_type_payload_and_related_url(): void
    {
        $buyer = $this->createBuyer();
        $order = $this->createOrderWithItem($buyer);

        $buyer->notify((new OrderCreatedNotification($order))->afterCommit());

        $notification = $buyer->notifications()->firstOrFail();

        $this->assertSame('marketplace.order.created', $notification->type);
        $this->assertSame('notifications.orders.created.title', $notification->data['title_key']);
        $this->assertSame('notifications.orders.created.message', $notification->data['message_key']);
        $this->assertSame('order', $notification->data['related_type']);
        $this->assertSame($order->id, $notification->data['related_id']);
        $this->assertSame(route('buyer.orders.show', $order, false), $notification->data['url']);
        $this->assertNotSame($notification->data['title_key'], __($notification->data['title_key'], $notification->data['title_params']));
    }

    public function test_product_moderation_notifications_target_admin_and_seller(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'is_active' => false,
        ]);

        $action = app(SendProductModerationNotificationAction::class);
        $action->moderationRequired($product);
        $action->approved($product);
        $action->rejected($product, 'Missing required label');

        Notification::assertSentTo($admin, ProductModerationRequiredNotification::class);
        Notification::assertSentTo($seller, ProductApprovedNotification::class);
        Notification::assertSentTo(
            $seller,
            ProductRejectedNotification::class,
            fn (ProductRejectedNotification $notification, array $channels): bool => $notification->reason === 'Missing required label'
                && in_array('mail', $channels, true),
        );
    }

    public function test_product_report_creation_notifies_active_admins(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        $report = app(CreateProductReportAction::class)->handle(
            product: $product,
            reason: ProductReportReason::Scam,
            message: 'This listing looks suspicious.',
            buyer: $buyer,
        );

        Notification::assertSentTo(
            $admin,
            ProductReportCreatedNotification::class,
            fn (ProductReportCreatedNotification $notification, array $channels): bool => $notification->report->is($report)
                && $channels === ['database'],
        );
        Notification::assertNotSentTo($seller, ProductReportCreatedNotification::class);
    }

    public function test_low_stock_notification_is_not_duplicated(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
            'stock' => 4,
        ]);

        $action = app(SendStockThresholdNotificationAction::class);
        $action->handle($product, previousStock: 10);
        $action->handle($product, previousStock: 10);

        $this->assertSame(
            1,
            $seller->notifications()
                ->where('type', 'marketplace.stock.low')
                ->where('data->related_id', $product->id)
                ->count(),
        );
    }

    public function test_notifications_can_be_marked_read_and_cross_user_access_is_forbidden(): void
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $order = $this->createOrderWithItem($buyer);

        $buyer->notify((new OrderCreatedNotification($order))->afterCommit());

        $notification = $buyer->notifications()->firstOrFail();

        $this->actingAs($otherBuyer, 'buyer')
            ->post(route('buyer.notifications.read', $notification))
            ->assertForbidden();

        $this->actingAs($buyer, 'buyer')
            ->post(route('buyer.notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_read_updates_only_current_users_notifications(): void
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $buyerOrder = $this->createOrderWithItem($buyer);
        $otherOrder = $this->createOrderWithItem($otherBuyer);

        $buyer->notify((new OrderCreatedNotification($buyerOrder))->afterCommit());
        $buyer->notify((new OrderCreatedNotification($buyerOrder))->afterCommit());
        $otherBuyer->notify((new OrderCreatedNotification($otherOrder))->afterCommit());

        $this->actingAs($buyer, 'buyer')
            ->post(route('buyer.notifications.read_all'))
            ->assertRedirect();

        $this->assertSame(0, $buyer->unreadNotifications()->count());
        $this->assertSame(1, $otherBuyer->unreadNotifications()->count());
    }

    public function test_notifications_index_is_filtered_to_authenticated_notifiable(): void
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $buyerOrder = $this->createOrderWithItem($buyer);
        $otherOrder = $this->createOrderWithItem($otherBuyer);

        $buyer->notify((new OrderCreatedNotification($buyerOrder))->afterCommit());
        $otherBuyer->notify((new OrderCreatedNotification($otherOrder))->afterCommit());

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.notifications.index'))
            ->assertOk()
            ->assertSee(__('notifications.ui.title'))
            ->assertSee(__('notifications.orders.created.title', ['order' => $buyerOrder->id]))
            ->assertDontSee(__('notifications.orders.created.title', ['order' => $otherOrder->id]));
    }

    public function test_seller_and_admin_notification_pages_render_guard_specific_rows(): void
    {
        $seller = $this->createSeller();
        $admin = $this->createAdmin();
        $sellerProduct = $this->createProduct(['seller_id' => $seller->id]);
        $adminProduct = $this->createProduct();

        $seller->notify((new LowStockNotification($sellerProduct, 'low', 5))->afterCommit());
        $admin->notify((new ProductModerationRequiredNotification($adminProduct))->afterCommit());

        $this->actingAs($seller, 'seller')
            ->get(route('seller.notifications.index'))
            ->assertOk()
            ->assertSee(__('notifications.stock.low.title'))
            ->assertDontSee(__('notifications.products.moderation_required.title'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee(__('notifications.products.moderation_required.title'))
            ->assertDontSee(__('notifications.stock.low.title'));
    }

    public function test_important_email_notifications_are_queueable_and_use_mail_channel(): void
    {
        $seller = $this->createSeller();
        $order = $this->createOrderWithItem(seller: $seller);
        $product = $this->createProduct(['seller_id' => $seller->id]);

        $this->assertInstanceOf(ShouldQueue::class, new NewOrderForSellerNotification($order));
        $this->assertInstanceOf(ShouldQueue::class, new ProductRejectedNotification($product, 'Missing label'));
        $this->assertInstanceOf(ShouldQueue::class, new LowStockNotification($product, 'low', 5));

        $this->assertContains('mail', (new NewOrderForSellerNotification($order))->via($seller));
        $this->assertContains('mail', (new ProductRejectedNotification($product, 'Missing label'))->via($seller));
        $this->assertNotContains('mail', (new LowStockNotification($product, 'low', 5))->via($seller));
    }
}
