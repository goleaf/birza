<?php

namespace Tests\Feature\Controllers\Api;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_json(): void
    {
        Product::factory()->active()->create(['name' => 'Test Product']);

        $response = $this->getJson('/api/product-search?query=Test');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'categories',
            'products',
        ]);
    }

    public function test_search_finds_products(): void
    {
        Product::factory()->active()->create(['name' => 'Apple']);
        Product::factory()->active()->create(['name' => 'Banana']);

        $response = $this->getJson('/api/product-search?query=Apple');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'products');
    }

    public function test_search_handles_empty_query(): void
    {
        $response = $this->getJson('/api/product-search?query=');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'categories',
            'products',
        ]);
    }
}

