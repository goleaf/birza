<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Livewire\Frontend\Buyer\Orders\Show as BuyerOrderShow;
use App\Livewire\Frontend\Seller\Orders\Show as SellerOrderShow;
use App\Models\OrderStatusHistory;
use App\Notifications\Marketplace\OrderStatusChangedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class OrderStatusWorkflowFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_seller_valid_status_transition_creates_history_and_notifies_buyer(): void
    {
        Notification::fake();

        $buyer = $this->createBuyer();
        $seller = $this->createSeller(['balance' => 0]);
        $order = $this->createOrderWithItem($buyer, $seller);

        $history = app(ChangeOrderStatusAction::class)->handle(
            order: $order,
            nextStatus: OrderStatus::Accepted,
            actor: $seller,
            note: 'Packed by seller',
        );

        $order->refresh();

        $this->assertSame(OrderStatus::Accepted, $order->status);
        $this->assertSame(OrderPaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Pending, $history->old_status);
        $this->assertSame(OrderStatus::Accepted, $history->new_status);
        $this->assertSame($seller->id, $history->changed_by_user_id);
        $this->assertSame(OrderStatusActorRole::Seller, $history->changed_by_role);
        $this->assertSame('Packed by seller', $history->note);

        Notification::assertSentTo($buyer, OrderStatusChangedNotification::class);
        Notification::assertNotSentTo($seller, OrderStatusChangedNotification::class);
    }

    public function test_invalid_status_transition_fails_without_history(): void
    {
        $buyer = $this->createBuyer();
        $order = $this->createOrderWithItem($buyer, orderAttributes: [
            'status' => OrderStatus::Completed,
            'payment_status' => OrderPaymentStatus::Paid,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(ChangeOrderStatusAction::class)->handle(
                order: $order,
                nextStatus: OrderStatus::Pending,
                actor: $buyer,
            );
        } finally {
            $this->assertSame(0, OrderStatusHistory::query()->count());
            $this->assertSame(OrderStatus::Completed, $order->refresh()->status);
        }
    }

    public function test_admin_status_change_requires_reason(): void
    {
        $admin = $this->createAdmin();
        $order = $this->createOrderWithItem();

        $this->expectException(ValidationException::class);

        app(ChangeOrderStatusAction::class)->handle(
            order: $order,
            nextStatus: OrderStatus::Rejected,
            actor: $admin,
        );
    }

    public function test_admin_can_reject_with_reason_and_notifies_buyer_and_seller(): void
    {
        Notification::fake();

        $admin = $this->createAdmin();
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $order = $this->createOrderWithItem($buyer, $seller);

        $history = app(ChangeOrderStatusAction::class)->handle(
            order: $order,
            nextStatus: OrderStatus::Rejected,
            actor: $admin,
            reason: 'Missing required documents',
        );

        $this->assertSame(OrderStatus::Rejected, $order->refresh()->status);
        $this->assertSame('Missing required documents', $history->reason);

        Notification::assertSentTo($buyer, OrderStatusChangedNotification::class);
        Notification::assertSentTo($seller, OrderStatusChangedNotification::class);
    }

    public function test_seller_cannot_change_another_sellers_order_status(): void
    {
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $order = $this->createOrderWithItem(seller: $otherSeller);

        $this->expectException(AuthorizationException::class);

        app(ChangeOrderStatusAction::class)->handle(
            order: $order,
            nextStatus: OrderStatus::Accepted,
            actor: $seller,
        );
    }

    public function test_buyer_and_seller_livewire_actions_respect_authorization(): void
    {
        $buyer = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $buyerOrder = $this->createOrderWithItem($buyer, $seller);
        $sellerOrder = $this->createOrderWithItem(seller: $otherSeller);

        $this->actingAs($otherBuyer, 'buyer');

        Livewire::test(BuyerOrderShow::class, ['order' => $buyerOrder])
            ->assertForbidden();

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerOrderShow::class, ['order' => $sellerOrder])
            ->assertForbidden();
    }
}
