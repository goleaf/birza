<?php

namespace Tests\Unit\Models;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_belongs_to_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $buyer->id]);

        $this->assertInstanceOf(Buyer::class, $cart->buyer);
        $this->assertEquals($buyer->id, $cart->buyer->id);
    }

    public function test_cart_has_many_cart_items(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

        $this->assertCount(3, $cart->cartItems);
        $this->assertInstanceOf(CartItem::class, $cart->cartItems->first());
    }

    public function test_cart_fillable_attributes(): void
    {
        $cart = new Cart;
        $fillable = $cart->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('quantity', $fillable);
    }
}
