<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Cart\CreateOrdersFromCartAction;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductShow;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class CartCheckoutFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_authenticated_buyer_can_add_active_product_to_cart(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'name' => 'Cart Milk',
            'stock' => 5,
            'min_order_count' => 2,
        ]);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerProductShow::class, ['product' => $product])
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('carts', [
            'user_id' => $buyer->id,
            'guest_token' => null,
            'status' => Cart::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '10.00',
        ]);
    }

    public function test_inactive_or_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $buyer = $this->createBuyer();
        $inactiveProduct = $this->createProduct([
            'is_active' => false,
            'stock' => 5,
        ]);
        $outOfStockProduct = $this->createProduct([
            'stock' => 0,
        ]);

        $this->actingAs($buyer, 'buyer');

        $this->get(route('buyer.products.show', $inactiveProduct))
            ->assertNotFound();

        Livewire::test(BuyerProductShow::class, ['product' => $outOfStockProduct])
            ->call('addToCart')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_quantity_can_be_updated_and_item_can_be_removed(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct([
            'stock' => 10,
            'min_order_count' => 1,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerCartIndex::class)
            ->set('quantities.'.$cartItem->id, 3)
            ->call('updateQuantity', $cartItem->id)
            ->assertHasNoErrors();

        $this->assertSame(3, $cartItem->refresh()->quantity);

        Livewire::test(BuyerCartIndex::class)
            ->call('removeItem', $cartItem->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_checkout_creates_order_from_fresh_backend_prices_and_clears_cart(): void
    {
        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 10',
        ]);
        $product = $this->createProduct([
            'price' => 20.00,
            'stock' => 10,
            'min_order_count' => 1,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 2, 0.01);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerCartIndex::class)
            ->call('checkout')
            ->assertRedirect(route('buyer.orders.index'));

        $order = Order::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $item = OrderItem::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertSame(OrderPaymentStatus::Pending, $order->payment_status);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('40.00', $order->order_total);
        $this->assertSame('Buyer Street 10', $order->shipping_address_snapshot);
        $this->assertSame('20.00', $item->unit_price);
        $this->assertSame('20.00', $item->product_price_snapshot);
        $this->assertSame('40.00', $item->total_price);
        $this->assertSame(2, $item->quantity);
        $this->assertSame(8, $product->refresh()->stock);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
        $this->assertSame(Cart::STATUS_CONVERTED, $cartItem->cart->refresh()->status);
    }

    public function test_failed_checkout_does_not_create_partial_order_or_clear_cart(): void
    {
        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 11',
        ]);
        $product = $this->createProduct([
            'stock' => 1,
            'min_order_count' => 1,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product, 2);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerCartIndex::class)
            ->call('checkout')
            ->assertHasErrors(['cart']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 2,
        ]);
        $this->assertSame(1, $product->refresh()->stock);
    }

    public function test_deleted_product_blocks_checkout(): void
    {
        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 12',
        ]);
        $product = $this->createProduct([
            'stock' => 5,
        ]);
        $cartItem = $this->createCartWithItem($buyer, $product);
        $product->delete();

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerCartIndex::class)
            ->call('checkout')
            ->assertHasErrors(['cart']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_empty_cart_cannot_move_to_checkout_confirmation(): void
    {
        $buyer = $this->createBuyer();

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerCartIndex::class)
            ->call('beginCheckout')
            ->assertSet('checkoutStep', 'review');
    }

    public function test_buyer_cannot_checkout_another_buyers_cart(): void
    {
        $buyer = $this->createBuyer([
            'address' => 'Buyer Street 13',
        ]);
        $otherBuyer = $this->createBuyer();
        $product = $this->createProduct();
        $cartItem = $this->createCartWithItem($otherBuyer, $product);

        $this->actingAs($buyer, 'buyer');

        $this->expectException(ValidationException::class);

        try {
            app(CreateOrdersFromCartAction::class)->handle($cartItem->cart, $buyer, [
                'shipping_address' => 'Buyer Street 13',
                'payment_method' => 'bank_transfer',
            ]);
        } finally {
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseHas('cart_items', [
                'id' => $cartItem->id,
            ]);
        }
    }
}
