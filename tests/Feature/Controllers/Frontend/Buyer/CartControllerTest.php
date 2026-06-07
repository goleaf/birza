<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Livewire\Frontend\Buyer\Cart\Index as BuyerCartIndex;
use App\Models\Product;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LukePOLO\LaraCart\Facades\LaraCart;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_index_requires_authentication(): void
    {
        $response = $this->get(route('buyer.cart.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_cart_index_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.cart.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerCartIndex::class)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_cart'))
            ->assertSee(__('cart_shopping_cart'))
            ->assertSee(__('cart_continue_shopping'))
            ->assertSee('badge-primary');
    }

    public function test_cart_index_renders_cart_items_from_component_data(): void
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
            'product_additional_image' => 'cart-test-apples-alt.webp',
        ]);

        LaraCart::destroyCart();

        try {
            $cartItem = LaraCart::add($product->id, $product->name, 2, $product->price, [
                'image' => $product->product_image,
                'unit' => $product->unit,
                'seller_id' => $product->seller_id,
                'category_id' => $product->category_id,
                'min_order_price' => $product->min_order_price,
                'min_order_count' => $product->min_order_count,
                'is_organic' => $product->is_organic,
                'country_of_origin' => $product->country_of_origin,
                'package_weight' => $product->package_weight,
                'price_per_liter' => $product->price_per_liter,
                'stock' => $product->stock,
            ]);

            $response = $this->actingAs($buyer, 'buyer')
                ->get(route('buyer.cart.index'));

            $response->assertStatus(200)
                ->assertSee($product->name)
                ->assertSee('wire:key="cart-item-'.$cartItem->getHash().'"', false)
                ->assertDontSee(__('cart_empty_cart'));
        } finally {
            LaraCart::destroyCart();
        }
    }
}
