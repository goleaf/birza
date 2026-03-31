<?php

namespace Tests\Feature\Seeders;

use App\Models\Attribute;
use App\Models\AttributeProduct;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\test_information\ProductAttributeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_attribute_seeder_only_links_allowed_categories_and_is_idempotent(): void
    {
        $mainCategory = Category::factory()->create();
        $allowedCategory = Category::factory()->create([
            'parent_category_id' => $mainCategory->id,
        ]);
        $blockedCategory = Category::factory()->create([
            'parent_category_id' => $mainCategory->id,
        ]);

        $attribute = Attribute::factory()->create();
        $attribute->categories()->sync([$allowedCategory->id]);

        $allowedValue = AttributeValue::query()->create([
            'attribute_id' => $attribute->id,
            'value' => ['en' => 'Value A', 'lt' => 'Reikšmė A'],
            'is_active' => true,
        ]);
        $fallbackValue = AttributeValue::query()->create([
            'attribute_id' => $attribute->id,
            'value' => ['en' => 'Value B', 'lt' => 'Reikšmė B'],
            'is_active' => true,
        ]);

        $allowedProduct = Product::factory()->create([
            'category_id' => $allowedCategory->id,
        ]);
        Product::factory()->create([
            'category_id' => $blockedCategory->id,
        ]);

        $this->seed(ProductAttributeSeeder::class);

        $this->assertSame(1, AttributeProduct::query()->count());

        $seededAttributeProduct = AttributeProduct::query()->firstOrFail();

        $this->assertSame($allowedProduct->id, $seededAttributeProduct->product_id);
        $this->assertSame($attribute->id, $seededAttributeProduct->attribute_id);
        $this->assertContains($seededAttributeProduct->selected_value_id, [
            $allowedValue->id,
            $fallbackValue->id,
        ]);

        $this->seed(ProductAttributeSeeder::class);

        $this->assertSame(1, AttributeProduct::query()->count());
    }
}
