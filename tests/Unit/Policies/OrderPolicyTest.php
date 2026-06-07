<?php

namespace Tests\Unit\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_view_and_cancel_only_own_pending_order(): void
    {
        $buyer = Buyer::factory()->verified()->active()->create();
        $otherBuyer = Buyer::factory()->verified()->active()->create();
        $order = Order::factory()->pending()->for($buyer, 'buyer')->create();

        $this->assertTrue($buyer->can('view', $order));
        $this->assertTrue($buyer->can('changeStatus', [$order, OrderStatus::Cancelled]));
        $this->assertFalse($otherBuyer->can('view', $order));
        $this->assertFalse($otherBuyer->can('changeStatus', [$order, OrderStatus::Cancelled]));
    }

    public function test_seller_can_view_and_accept_order_with_own_items_only(): void
    {
        $seller = Seller::factory()->verified()->active()->create();
        $otherSeller = Seller::factory()->verified()->active()->create();
        $buyer = Buyer::factory()->verified()->active()->create();
        $product = Product::factory()->for($seller, 'seller')->create();
        $order = Order::factory()->pending()->for($buyer, 'buyer')->create();

        OrderItem::factory()
            ->for($order)
            ->forProduct($product)
            ->create();

        $this->assertTrue($seller->can('view', $order));
        $this->assertTrue($seller->can('changeStatus', [$order, OrderStatus::Accepted]));
        $this->assertFalse($otherSeller->can('view', $order));
        $this->assertFalse($otherSeller->can('changeStatus', [$order, OrderStatus::Accepted]));
    }

    public function test_admin_can_view_and_manage_orders(): void
    {
        $admin = Admin::factory()->active()->create();
        $order = Order::factory()->pending()->create();

        $this->assertTrue($admin->can('view', $order));
        $this->assertTrue($admin->can('manage', $order));
        $this->assertTrue($admin->can('changeStatus', [$order, OrderStatus::Cancelled]));
    }
}
