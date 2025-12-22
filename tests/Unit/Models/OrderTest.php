<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Users\Buyer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        $order = Order::factory()->create(['buyer_id' => $buyer->id]);

        $this->assertInstanceOf(Buyer::class, $order->buyer);
        $this->assertEquals($buyer->id, $order->buyer->id);
    }

    public function test_order_has_many_order_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->orderItems);
    }

    public function test_order_has_many_sellers_through_order_items(): void
    {
        $order = Order::factory()->create();
        $seller = Seller::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertCount(1, $order->sellers);
        $this->assertEquals($seller->id, $order->sellers->first()->id);
    }

    public function test_order_pending_scope(): void
    {
        Order::factory()->pending()->create();
        Order::factory()->paid()->create();

        $pendingOrders = Order::pending()->get();

        $this->assertCount(1, $pendingOrders);
        $this->assertEquals(Order::STATUS['PENDING'], $pendingOrders->first()->status);
    }

    public function test_order_paid_scope(): void
    {
        Order::factory()->paid()->create();
        Order::factory()->pending()->create();

        $paidOrders = Order::paid()->get();

        $this->assertCount(1, $paidOrders);
        $this->assertEquals(Order::STATUS['PAID'], $paidOrders->first()->status);
    }

    public function test_order_total_attribute(): void
    {
        $order = Order::factory()->create(['order_total' => 100.50]);

        $this->assertEquals(100.50, $order->total);
        $this->assertIsFloat($order->total);
    }

    public function test_order_soft_deletes(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        $order->delete();

        $this->assertSoftDeleted('orders', ['id' => $orderId]);
    }

    public function test_order_belongs_to_many_products(): void
    {
        $order = Order::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
            ]);
        }

        $this->assertCount(3, $order->products);
    }
}

