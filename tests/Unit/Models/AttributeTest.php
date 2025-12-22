<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_has_many_values(): void
    {
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->count(3)->create(['attribute_id' => $attribute->id]);

        $this->assertCount(3, $attribute->values);
    }

    public function test_attribute_belongs_to_many_categories(): void
    {
        $attribute = Attribute::factory()->create();
        $categories = Category::factory()->count(3)->create();

        $attribute->categories()->attach($categories->pluck('id'));

        $this->assertCount(3, $attribute->categories);
    }

    public function test_attribute_active_scope(): void
    {
        Attribute::factory()->active()->create();
        Attribute::factory()->create(['is_active' => false]);

        $activeAttributes = Attribute::active()->get();

        $this->assertCount(1, $activeAttributes);
    }

    public function test_attribute_filterable_scope(): void
    {
        Attribute::factory()->create(['is_filterable' => true]);
        Attribute::factory()->create(['is_filterable' => false]);

        $filterableAttributes = Attribute::filterable()->get();

        $this->assertCount(1, $filterableAttributes);
    }

    public function test_attribute_translatable_fields(): void
    {
        $attribute = Attribute::factory()->create([
            'name' => [
                'en' => 'Color',
                'lt' => 'Spalva',
            ],
        ]);

        $this->assertEquals('Color', $attribute->getTranslation('name', 'en'));
        $this->assertEquals('Spalva', $attribute->getTranslation('name', 'lt'));
    }
}

