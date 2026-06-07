<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use App\Livewire\Frontend\Seller\Orders\Show as SellerOrderShow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_index_requires_authentication(): void
    {
        $response = $this->get(route('seller.orders.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_order_index_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.orders.index'));

        $response->assertStatus(200);
    }

    public function test_order_cancel_confirmation_uses_modal_flow(): void
    {
        $seller = Seller::factory()->create([
            'balance' => 0,
        ]);
        $buyer = Buyer::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'payment_status' => Order::STATUS['PENDING'],
            'status' => Order::STATUS['PENDING'],
        ]);

        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 12.50,
            'total_price' => 25.00,
        ]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerOrderShow::class, ['order' => $order])
            ->call('confirmCancelOrder')
            ->assertSet('confirmModal', true)
            ->assertSet('confirmModalMethod', 'updateStatus')
            ->call('runConfirmedAction')
            ->assertSet('confirmModal', false);

        $order->refresh();

        $this->assertSame(Order::STATUS['CANCELLED'], $order->payment_status);
        $this->assertSame(Order::STATUS['CANCELLED'], $order->status);
        $this->assertSame(0.0, (float) $seller->fresh()->balance);
    }
}
