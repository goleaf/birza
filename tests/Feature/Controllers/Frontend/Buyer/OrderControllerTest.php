<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Livewire\Frontend\Buyer\Orders\Show as BuyerOrderShow;
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
        $response = $this->get(route('buyer.orders.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_order_index_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        Order::factory()->count(3)->create(['buyer_id' => $buyer->id]);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.orders.index'));

        $response->assertStatus(200);
    }

    public function test_order_cancel_confirmation_uses_modal_flow(): void
    {
        $buyer = Buyer::factory()->create();
        $seller = Seller::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'stock' => 10,
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'payment_status' => Order::STATUS['PENDING'],
            'status' => Order::STATUS['PENDING'],
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'quantity' => 2,
        ]);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerOrderShow::class, ['order' => $order])
            ->call('confirmCancelOrder')
            ->assertSet('confirmModal', true)
            ->assertSet('confirmModalMethod', 'cancelOrder')
            ->call('runConfirmedAction')
            ->assertSet('confirmModal', false);

        $order->refresh();
        $product->refresh();

        $this->assertSame(Order::STATUS['CANCELLED'], $order->payment_status);
        $this->assertSame(Order::STATUS['CANCELLED'], $order->status);
        $this->assertSame(12, $product->stock);
    }
}
