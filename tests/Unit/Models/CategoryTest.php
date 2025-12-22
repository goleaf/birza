<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->products);
    }

    public function test_category_belongs_to_parent(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_category_id' => $parent->id]);

        $this->assertInstanceOf(Category::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_category_has_many_subcategories(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->count(3)->create(['parent_category_id' => $parent->id]);

        $this->assertCount(3, $parent->subcategories);
    }

    public function test_category_belongs_to_many_attributes(): void
    {
        $category = Category::factory()->create();
        $attributes = Attribute::factory()->count(3)->create();

        $category->attributes()->attach($attributes->pluck('id'));

        $this->assertCount(3, $category->attributes);
    }

    public function test_category_belongs_to_many_sellers(): void
    {
        $category = Category::factory()->create();
        $sellers = Seller::factory()->count(3)->create();

        $category->sellers()->attach($sellers->pluck('id'));

        $this->assertCount(3, $category->sellers);
    }

    public function test_category_translatable_fields(): void
    {
        $category = Category::factory()->create([
            'category_name' => [
                'en' => 'Electronics',
                'lt' => 'Elektronika',
            ],
        ]);

        $this->assertEquals('Electronics', $category->getTranslation('category_name', 'en'));
        $this->assertEquals('Elektronika', $category->getTranslation('category_name', 'lt'));
    }

    public function test_category_get_all_products_count_attribute(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_category_id' => $parent->id]);

        Product::factory()->count(2)->create(['category_id' => $parent->id]);
        Product::factory()->count(3)->create(['category_id' => $child->id]);

        $this->assertEquals(5, $parent->all_products_count);
    }
}

