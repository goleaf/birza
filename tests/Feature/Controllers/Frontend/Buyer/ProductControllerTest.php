<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Livewire\Frontend\Buyer\Products\Index as BuyerProductsIndex;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductsShow;
use App\Models\Product;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_requires_authentication(): void
    {
        Product::factory()->active()->count(5)->create();

        $response = $this->get(route('buyer.products.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_product_index_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        Product::factory()->active()->count(5)->create();

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerProductsIndex::class);
    }

    public function test_product_show_requires_authentication(): void
    {
        $product = Product::factory()->active()->create();

        $response = $this->get(route('buyer.products.show', $product));

        $response->assertRedirect(route('home'));
    }

    public function test_product_show_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        $product = Product::factory()->active()->create();

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.show', $product));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerProductsShow::class);
    }
}
