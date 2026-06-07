<?php

namespace Tests\Feature;

use App\Actions\Cart\CreateOrdersFromCartAction;
use App\Actions\ProductBundles\AddBundleToCartAction;
use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Livewire\Frontend\Buyer\ProductBundles\Show as BuyerBundleShow;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class BundleCartCheckoutTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_buyer_can_add_active_bundle_to_cart(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 10.00, 'stock' => 10]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 15.00, 'stock' => 10]);
        $bundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]]);

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerBundleShow::class, ['productBundle' => $bundle])
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cart_bundle_items', [
            'product_bundle_id' => $bundle->id,
            'quantity' => 2,
            'unit_price' => '25.00',
        ]);
    }

    public function test_inactive_bundle_cannot_be_added_to_cart(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id]);
        $bundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]], [
            'status' => ProductBundle::STATUS_INACTIVE,
            'published_at' => null,
        ]);

        $this->expectException(ValidationException::class);

        app(AddBundleToCartAction::class)->handle($bundle, 1, $buyer);
    }

    public function test_bundle_cart_quantity_can_be_updated_and_removed(): void
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'stock' => 10]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'stock' => 10]);
        $bundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]]);
        $cart = app(AddBundleToCartAction::class)->handle($bundle, 1, $buyer);
        $cartBundleItem = $cart->bundleItems()->firstOrFail();

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerCartIndex::class)
            ->set('bundleQuantities.'.$cartBundleItem->id, 2)
            ->call('updateBundleQuantity', $cartBundleItem->id)
            ->assertHasNoErrors();

        $this->assertSame(2, $cartBundleItem->refresh()->quantity);

        Livewire::test(BuyerCartIndex::class)
            ->call('removeBundleItem', $cartBundleItem->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('cart_bundle_items', [
            'id' => $cartBundleItem->id,
        ]);
    }

    public function test_checkout_recalculates_bundle_price_creates_snapshot_and_decrements_stock(): void
    {
        $buyer = $this->createBuyer(['address' => 'Bundle Buyer Street 1']);
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 10.00, 'stock' => 8]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 20.00, 'stock' => 8]);
        $bundle = $this->createBundle($seller, [[$firstProduct, 2], [$secondProduct, 1]], [
            'discount_type' => ProductBundle::DISCOUNT_TYPE_FIXED_AMOUNT,
            'discount_value' => 5,
        ]);
        $cart = app(AddBundleToCartAction::class)->handle($bundle, 1, $buyer);
        $cart->bundleItems()->firstOrFail()->update(['unit_price' => 0.01]);
        $firstProduct->forceFill(['price' => 12.00])->save();

        $orders = app(CreateOrdersFromCartAction::class)->handle($cart, $buyer, [
            'shipping_address' => 'Bundle Buyer Street 1',
            'payment_method' => 'bank_transfer',
        ]);

        $order = $orders->first();
        $orderBundle = $order->orderBundles->first();

        $this->assertCount(1, $orders);
        $this->assertSame('44.00', $order->subtotal);
        $this->assertSame('5.00', $order->discount_total);
        $this->assertSame('39.00', $order->order_total);
        $this->assertSame($bundle->id, $orderBundle->product_bundle_id);
        $this->assertSame($bundle->name, $orderBundle->bundle_name_snapshot);
        $this->assertSame('44.00', $orderBundle->base_price);
        $this->assertSame('39.00', $orderBundle->final_price);
        $this->assertCount(2, $orderBundle->products_snapshot);
        $this->assertSame(6, $firstProduct->refresh()->stock);
        $this->assertSame(7, $secondProduct->refresh()->stock);
        $this->assertSame(0, $cart->fresh('bundleItems')->bundleItems()->count());
        $this->assertSame(Cart::STATUS_CONVERTED, $cart->refresh()->status);
    }

    public function test_checkout_blocks_unavailable_bundle_without_clearing_cart(): void
    {
        $buyer = $this->createBuyer(['address' => 'Bundle Buyer Street 2']);
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'stock' => 1]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'stock' => 5]);
        $bundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]]);
        $cart = app(AddBundleToCartAction::class)->handle($bundle, 1, $buyer);
        $firstProduct->forceFill(['stock' => 0])->save();

        $this->expectException(ValidationException::class);

        try {
            app(CreateOrdersFromCartAction::class)->handle($cart, $buyer, [
                'shipping_address' => 'Bundle Buyer Street 2',
                'payment_method' => 'bank_transfer',
            ]);
        } finally {
            $this->assertDatabaseCount('orders', 0);
            $this->assertSame(1, $cart->fresh('bundleItems')->bundleItems()->count());
        }
    }

    public function test_order_bundle_snapshot_survives_deleted_bundle(): void
    {
        $buyer = $this->createBuyer(['address' => 'Bundle Buyer Street 3']);
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'stock' => 5]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'stock' => 5]);
        $bundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]], [
            'name' => 'Deleted Bundle Snapshot Set',
        ]);
        $cart = app(AddBundleToCartAction::class)->handle($bundle, 1, $buyer);

        $orders = app(CreateOrdersFromCartAction::class)->handle($cart, $buyer, [
            'shipping_address' => 'Bundle Buyer Street 3',
            'payment_method' => 'bank_transfer',
        ]);
        $order = $orders->first();

        $bundle->delete();

        $snapshot = Order::query()
            ->with('orderBundles.items.product')
            ->findOrFail($order->id)
            ->orderBundles
            ->first();

        $this->assertSame('Deleted Bundle Snapshot Set', $snapshot->bundle_name_snapshot);
        $this->assertTrue($snapshot->fresh('productBundle')->productBundle?->trashed());
        $this->assertCount(2, $snapshot->products_snapshot);
    }

    /**
     * @param  array<int, array{0: Product, 1: int}>  $products
     * @param  array<string, mixed>  $attributes
     */
    private function createBundle(Seller $seller, array $products, array $attributes = []): ProductBundle
    {
        $bundle = ProductBundle::factory()
            ->for($seller, 'seller')
            ->active()
            ->create(array_merge([
                'slug' => 'cart-bundle-'.fake()->unique()->numberBetween(1000, 9999),
            ], $attributes));

        foreach ($products as $index => [$product, $quantity]) {
            ProductBundleItem::factory()
                ->forBundle($bundle, $product, $quantity)
                ->create([
                    'sort_order' => $index,
                ]);
        }

        return $bundle->refresh()->load('seller', 'items.product.seller');
    }
}
