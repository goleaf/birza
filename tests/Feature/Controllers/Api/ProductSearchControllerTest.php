<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_json(): void
    {
        Product::factory()->active()->create(['name' => 'Test Product']);

        $response = $this->getJson('/api/products/search?query=Test');

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

        $response = $this->getJson('/api/products/search?query=Apple');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'products');
    }

    public function test_search_finds_products_by_localized_description(): void
    {
        Product::factory()->active()->create([
            'name' => 'Crate',
            'description' => [
                'en' => 'Fresh orchard apples',
                'lt' => 'Sviezios sodo kriauses',
            ],
        ]);

        Product::factory()->active()->create([
            'name' => 'Box',
            'description' => [
                'en' => 'Dried herbs',
                'lt' => 'Dziovintos zoleles',
            ],
        ]);

        $response = $this->getJson('/api/products/search?query=orchard&locale=en');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'Crate');
    }

    public function test_search_finds_localized_subcategories(): void
    {
        $parent = Category::factory()->create([
            'category_name' => [
                'en' => 'Dairy',
                'lt' => 'Pienas',
            ],
        ]);

        Category::factory()->create([
            'parent_category_id' => $parent->id,
            'category_name' => [
                'en' => 'Cheese',
                'lt' => 'Suris',
            ],
        ]);

        Category::factory()->create([
            'parent_category_id' => $parent->id,
            'category_name' => [
                'en' => 'Milk',
                'lt' => 'Pienas',
            ],
        ]);

        $response = $this->getJson('/api/products/search?query=Sur&locale=lt');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('categories.0.category_name', 'Pienas > Suris');
    }

    public function test_search_handles_empty_query_without_broad_results(): void
    {
        Product::factory()->active()->create(['name' => 'Visible Product']);

        $response = $this->getJson('/api/products/search?query=');

        $response
            ->assertStatus(200)
            ->assertExactJson([
                'categories' => [],
                'products' => [],
            ]);
    }

    public function test_search_rejects_unknown_locale(): void
    {
        $response = $this->getJson('/api/products/search?query=Apple&locale=de');

        $response->assertUnprocessable();
    }
}
