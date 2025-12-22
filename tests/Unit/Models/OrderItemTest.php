<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_item_belongs_to_order(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $item->order);
        $this->assertEquals($order->id, $item->order->id);
    }

    public function test_order_item_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $item = OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $item->product);
        $this->assertEquals($product->id, $item->product->id);
    }

    public function test_order_item_belongs_to_seller(): void
    {
        $seller = Seller::factory()->create();
        $item = OrderItem::factory()->create(['seller_id' => $seller->id]);

        $this->assertInstanceOf(Seller::class, $item->seller);
        $this->assertEquals($seller->id, $item->seller->id);
    }

    public function test_order_item_soft_deletes(): void
    {
        $item = OrderItem::factory()->create();
        $itemId = $item->id;

        $item->delete();

        $this->assertSoftDeleted('order_items', ['id' => $itemId]);
    }

    public function test_order_item_casts(): void
    {
        $item = OrderItem::factory()->create([
            'unit_price' => '10.50',
            'total_price' => '105.00',
            'quantity' => '5',
        ]);

        $this->assertIsFloat($item->unit_price);
        $this->assertIsFloat($item->total_price);
        $this->assertIsInt($item->quantity);
    }
}

