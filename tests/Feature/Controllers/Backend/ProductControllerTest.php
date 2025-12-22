<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Product;
use App\Models\Category;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.products.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_product_index_displays_products(): void
    {
        $admin = Admin::factory()->create();
        Product::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.products.index'));

        $response->assertStatus(200);
    }

    public function test_product_create_form_displays(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.products.create'));

        $response->assertStatus(200);
    }
}

