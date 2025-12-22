<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\Users\Buyer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_belongs_to_user(): void
    {
        $buyer = Buyer::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $buyer->id]);

        $this->assertInstanceOf(Buyer::class, $cart->user);
        $this->assertEquals($buyer->id, $cart->user->id);
    }

    public function test_cart_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $cart->product);
        $this->assertEquals($product->id, $cart->product->id);
    }

    public function test_cart_fillable_attributes(): void
    {
        $cart = new Cart();
        $fillable = $cart->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('quantity', $fillable);
    }
}

