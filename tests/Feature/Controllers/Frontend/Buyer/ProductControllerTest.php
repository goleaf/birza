<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use Tests\TestCase;
use App\Models\Users\Buyer;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_displays_products(): void
    {
        Product::factory()->active()->count(5)->create();

        $response = $this->get(route('buyer.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.buyer.products.index');
    }

    public function test_product_show_displays_product(): void
    {
        $product = Product::factory()->active()->create();

        $response = $this->get(route('buyer.products.show', $product));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.buyer.products.show');
    }
}

