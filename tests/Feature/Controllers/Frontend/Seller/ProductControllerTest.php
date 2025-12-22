<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use Tests\TestCase;
use App\Models\Users\Seller;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_requires_authentication(): void
    {
        $response = $this->get(route('seller.products.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_product_index_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        Product::factory()->count(3)->create(['seller_id' => $seller->id]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.seller.products.index');
    }
}

