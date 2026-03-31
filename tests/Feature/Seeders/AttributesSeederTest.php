<?php

namespace Tests\Feature\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Database\Seeders\test_information\AttributesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_attributes_seeder_is_idempotent(): void
    {
        Product::factory()->count(2)->create();

        $this->seed(AttributesSeeder::class);

        $initialAttributeCount = Attribute::query()->count();
        $initialAttributeValueCount = AttributeValue::query()->count();
        $initialProductAttributeValueCount = ProductAttributeValue::query()->count();

        $this->assertGreaterThan(0, $initialAttributeCount);
        $this->assertGreaterThan(0, $initialAttributeValueCount);
        $this->assertSame(
            Product::query()->count() * $initialAttributeValueCount,
            $initialProductAttributeValueCount
        );

        $this->seed(AttributesSeeder::class);

        $this->assertSame($initialAttributeCount, Attribute::query()->count());
        $this->assertSame($initialAttributeValueCount, AttributeValue::query()->count());
        $this->assertSame($initialProductAttributeValueCount, ProductAttributeValue::query()->count());
    }
}
