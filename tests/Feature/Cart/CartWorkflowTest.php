<?php

namespace Tests\Feature\Cart;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\CalculateCartTotalsAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\CreateOrdersFromCartAction;
use App\Actions\Cart\MergeGuestCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemQuantityAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_item_to_cart(): void
    {
        $product = $this->activeProduct(price: 12.50, stock: 10);

        $cart = app(AddCartItemAction::class)->handle(
            product: $product,
            quantity: 2,
            buyer: null,
            guestToken: 'guest-token-1',
        );

        $this->assertNull($cart->user_id);
        $this->assertSame('guest-token-1', $cart->guest_token);
        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(2, $cart->items->first()->quantity);
        $this->assertSame('12.50', $cart->items->first()->unit_price);
    }

    public function test_authenticated_buyer_can_add_item_to_cart(): void
    {
        $buyer = Buyer::factory()->create();
        $product = $this->activeProduct(price: 7.25, stock: 10);

        $cart = app(AddCartItemAction::class)->handle(
            product: $product,
            quantity: 3,
            buyer: $buyer,
        );

        $this->assertSame($buyer->id, $cart->user_id);
        $this->assertNull($cart->guest_token);
        $this->assertSame(3, $cart->items->first()->quantity);
    }

    public function test_cart_merges_after_login(): void
    {
        $buyer = Buyer::factory()->create();
        $product = $this->activeProduct(price: 5.00, stock: 10);

        app(AddCartItemAction::class)->handle($product, 2, null, 'guest-token-2');
        app(AddCartItemAction::class)->handle($product, 3, $buyer);

        $cart = app(MergeGuestCartAction::class)->handle('guest-token-2', $buyer);

        $this->assertSame($buyer->id, $cart->user_id);
        $this->assertSame(5, $cart->items->first()->quantity);
        $this->assertDatabaseMissing('carts', ['guest_token' => 'guest-token-2', 'status' => 'active']);
    }

    public function test_quantity_can_be_changed_and_item_can_be_removed(): void
    {
        $buyer = Buyer::factory()->create();
        $product = $this->activeProduct(stock: 10);
        $cart = app(AddCartItemAction::class)->handle($product, 2, $buyer);

        app(UpdateCartItemQuantityAction::class)->handle($cart, $product, 4);
        $this->assertSame(4, $cart->fresh('items')->items->first()->quantity);

        app(UpdateCartItemQuantityAction::class)->handle($cart, $product, 1);
        $this->assertSame(1, $cart->fresh('items')->items->first()->quantity);

        app(RemoveCartItemAction::class)->handle($cart, $product);
        $this->assertSame(0, $cart->fresh('items')->items()->count());
    }

    public function test_cart_can_be_cleared_and_totals_are_calculated_from_backend_prices(): void
    {
        $buyer = Buyer::factory()->create();
        $firstProduct = $this->activeProduct(price: 3.00, stock: 10);
        $secondProduct = $this->activeProduct(price: 4.50, stock: 10);

        $cart = app(AddCartItemAction::class)->handle($firstProduct, 2, $buyer);
        app(AddCartItemAction::class)->handle($secondProduct, 3, $buyer);

        $totals = app(CalculateCartTotalsAction::class)->handle($cart);
        $this->assertSame(5, $totals['item_count']);
        $this->assertSame('19.50', $totals['subtotal']);
        $this->assertSame('19.50', $totals['total']);

        app(ClearCartAction::class)->handle($cart);
        $this->assertSame(0, $cart->fresh('items')->items()->count());
    }

    public function test_checkout_blocks_invalid_cart_items_without_clearing_cart(): void
    {
        $buyer = Buyer::factory()->create();
        $product = $this->activeProduct(price: 8.00, stock: 1);
        $cart = app(AddCartItemAction::class)->handle($product, 1, $buyer);

        $cart->items()->first()->update(['quantity' => 3]);

        $this->expectException(ValidationException::class);

        try {
            app(CreateOrdersFromCartAction::class)->handle($cart, $buyer, [
                'shipping_address' => 'Buyer Street 1',
                'payment_method' => 'bank_transfer',
            ]);
        } finally {
            $this->assertSame(0, Order::query()->count());
            $this->assertSame(1, $cart->fresh('items')->items()->count());
        }
    }

    public function test_checkout_recalculates_price_creates_snapshots_and_clears_cart(): void
    {
        $buyer = Buyer::factory()->create(['address' => 'Buyer Street 2']);
        $product = $this->activeProduct(name: 'Snapshot Cheese', price: 10.00, stock: 10);
        $cart = app(AddCartItemAction::class)->handle($product, 2, $buyer);

        $cart->items()->first()->update(['unit_price' => 1.00]);

        /** @var Collection<int, Order> $orders */
        $orders = app(CreateOrdersFromCartAction::class)->handle($cart, $buyer, [
            'shipping_address' => 'Buyer Street 2',
            'payment_method' => 'bank_transfer',
        ]);

        $order = $orders->first();
        $item = $order->items->first();

        $this->assertCount(1, $orders);
        $this->assertSame('20.00', $order->order_total);
        $this->assertSame('Snapshot Cheese', $item->product_title_snapshot);
        $this->assertSame('10.00', $item->product_price_snapshot);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('Buyer Street 2', $order->shipping_address_snapshot);
        $this->assertSame(8, $product->fresh()->stock);
        $this->assertSame(0, $cart->fresh('items')->items()->count());
    }

    public function test_multi_seller_checkout_creates_one_order_per_seller(): void
    {
        $buyer = Buyer::factory()->create(['address' => 'Buyer Street 3']);
        $firstProduct = $this->activeProduct(price: 6.00, stock: 10);
        $secondProduct = $this->activeProduct(price: 9.00, stock: 10);
        $cart = app(AddCartItemAction::class)->handle($firstProduct, 1, $buyer);
        app(AddCartItemAction::class)->handle($secondProduct, 2, $buyer);

        $orders = app(CreateOrdersFromCartAction::class)->handle($cart, $buyer, [
            'shipping_address' => 'Buyer Street 3',
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertCount(2, $orders);
        $this->assertSame(['6.00', '18.00'], $orders->pluck('order_total')->sort()->values()->all());
        $this->assertCount(2, $orders->pluck('items.0.seller_id')->unique());
    }

    private function activeProduct(
        ?string $name = null,
        float $price = 10.00,
        int $stock = 10,
    ): Product {
        return Product::factory()
            ->for(Seller::factory()->active(), 'seller')
            ->active()
            ->create([
                'name' => $name ?? 'Cart Product',
                'price' => $price,
                'stock' => $stock,
                'min_order_count' => 1,
            ]);
    }
}
