<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Actions\Cart\AddCartItemAction;
use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Models\Product;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_index_displays_for_guest(): void
    {
        $response = $this->withSession(['cart_guest_token' => 'guest-cart-route'])
            ->get(route('buyer.cart.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerCartIndex::class)
            ->assertSee(__('common_cart'))
            ->assertSee(__('cart_shopping_cart'))
            ->assertSee(__('cart_empty_cart'));
    }

    public function test_cart_index_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.cart.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerCartIndex::class)
            ->assertSee(__('common_cart'))
            ->assertSee(__('cart_shopping_cart'))
            ->assertSee(__('cart_continue_shopping'));
    }

    public function test_cart_index_renders_database_cart_items(): void
    {
        $buyer = Buyer::factory()->create();
        $product = Product::factory()->active()->create([
            'name' => 'Cart Test Apples',
            'price' => 12.50,
            'min_order_count' => 1,
            'stock' => 10,
            'unit' => 'kg',
            'is_organic' => false,
            'product_image' => 'cart-test-apples.webp',
        ]);

        app(AddCartItemAction::class)->handle($product, 2, $buyer);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.cart.index'));

        $response->assertStatus(200)
            ->assertSee($product->name)
            ->assertSee('wire:key="cart-item-', false)
            ->assertDontSee(__('cart_empty_cart'))
            ->assertSee('aria-label="'.__('common_cart').' 2"', false);
    }
}
