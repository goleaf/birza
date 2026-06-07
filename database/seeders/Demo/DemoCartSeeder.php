<?php

namespace Database\Seeders\Demo;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\Users\Buyer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoCartSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('cart_items')) {
            return;
        }

        $this->emptyCart('demo-empty-buyer@example.com');
        $this->filledCart('buyer@example.com');
        $this->filledCart('demo-cart-buyer@example.com');
        $this->guestCart();
        $this->failedCheckoutCart();
    }

    private function emptyCart(string $buyerEmail): Cart
    {
        $buyer = Buyer::query()->where('email', $buyerEmail)->firstOrFail();

        return Cart::query()->updateOrCreate([
            'user_id' => $buyer->id,
            'status' => Cart::STATUS_ACTIVE,
        ], [
            'guest_token' => null,
        ]);
    }

    private function filledCart(string $buyerEmail): void
    {
        $buyer = Buyer::query()->where('email', $buyerEmail)->firstOrFail();
        $cart = Cart::query()->updateOrCreate([
            'user_id' => $buyer->id,
            'status' => Cart::STATUS_ACTIVE,
        ], [
            'guest_token' => null,
        ]);

        $this->cartItem($cart, 'Demo Active Apples', 2);
        $this->cartItem($cart, 'Demo Changed Price Cheese', 1, 10.00);
        $this->cartItem($cart, 'Demo Seller Two Bread', 3);
        $this->cartBundleItem($cart, 'demo-weekend-fruit-cheese-set', 1);
    }

    private function guestCart(): void
    {
        $cart = Cart::query()->updateOrCreate([
            'guest_token' => 'demo-guest-cart',
            'status' => Cart::STATUS_ACTIVE,
        ], [
            'user_id' => null,
        ]);

        $this->cartItem($cart, 'Demo Active Apples', 1);
    }

    private function failedCheckoutCart(): void
    {
        $cart = Cart::query()->updateOrCreate([
            'guest_token' => 'demo-failed-checkout-cart',
            'status' => Cart::STATUS_ACTIVE,
        ], [
            'user_id' => null,
        ]);

        $this->cartItem($cart, 'Demo Inactive Honey', 1);
        $this->cartItem($cart, 'Demo Out Of Stock Milk', 1);
    }

    private function cartItem(Cart $cart, string $productName, int $quantity, ?float $unitPrice = null): void
    {
        $product = Product::withTrashed()
            ->where('name', $productName)
            ->firstOrFail();

        $cart->items()->updateOrCreate([
            'product_id' => $product->id,
        ], [
            'quantity' => $quantity,
            'unit_price' => $unitPrice ?? (float) $product->price,
        ]);
    }

    private function cartBundleItem(Cart $cart, string $bundleSlug, int $quantity): void
    {
        if (! Schema::hasTable('cart_bundle_items')) {
            return;
        }

        $bundle = ProductBundle::query()
            ->where('slug', $bundleSlug)
            ->first();

        if (! $bundle) {
            return;
        }

        $cart->bundleItems()->updateOrCreate([
            'product_bundle_id' => $bundle->id,
        ], [
            'quantity' => $quantity,
            'unit_price' => $bundle->finalPrice(),
        ]);
    }
}
