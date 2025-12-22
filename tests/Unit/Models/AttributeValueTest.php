<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\AttributeValue;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttributeValueTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_value_belongs_to_attribute(): void
    {
        $attribute = Attribute::factory()->create();
        $value = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $this->assertInstanceOf(Attribute::class, $value->attribute);
        $this->assertEquals($attribute->id, $value->attribute->id);
    }

    public function test_attribute_value_belongs_to_many_products(): void
    {
        $value = AttributeValue::factory()->create();
        $products = Product::factory()->count(3)->create();

        $value->products()->attach($products->pluck('id'));

        $this->assertCount(3, $value->products);
    }

    public function test_attribute_value_active_scope(): void
    {
        AttributeValue::factory()->active()->create();
        AttributeValue::factory()->create(['is_active' => false]);

        $activeValues = AttributeValue::active()->get();

        $this->assertCount(1, $activeValues);
    }

    public function test_attribute_value_translatable_fields(): void
    {
        $value = AttributeValue::factory()->create([
            'value' => [
                'en' => 'Red',
                'lt' => 'Raudona',
            ],
        ]);

        $this->assertEquals('Red', $value->getTranslation('value', 'en'));
        $this->assertEquals('Raudona', $value->getTranslation('value', 'lt'));
    }
}

