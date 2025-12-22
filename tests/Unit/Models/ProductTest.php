<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Users\Seller;
use App\Models\Country;
use App\Models\AttributeValue;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_belongs_to_seller(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $this->assertInstanceOf(Seller::class, $product->seller);
        $this->assertEquals($seller->id, $product->seller->id);
    }

    public function test_product_belongs_to_country(): void
    {
        $country = Country::factory()->create();
        $product = Product::factory()->create(['country_of_origin' => $country->id]);

        $this->assertInstanceOf(Country::class, $product->country);
        $this->assertEquals($country->id, $product->country->id);
    }

    public function test_product_has_many_order_items(): void
    {
        $product = Product::factory()->create();
        OrderItem::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->orderItems);
        $this->assertInstanceOf(OrderItem::class, $product->orderItems->first());
    }

    public function test_product_belongs_to_many_attribute_values(): void
    {
        $product = Product::factory()->create();
        $attributeValues = AttributeValue::factory()->count(3)->create();

        $product->attributeValues()->attach($attributeValues->pluck('id'));

        $this->assertCount(3, $product->attributeValues);
    }

    public function test_product_sync_attribute_values(): void
    {
        $product = Product::factory()->create();
        $attributeValues = AttributeValue::factory()->count(3)->create();

        $product->syncAttributeValues($attributeValues->pluck('id')->toArray());

        $this->assertCount(3, $product->attributeValues);
    }

    public function test_product_active_scope(): void
    {
        Product::factory()->count(3)->active()->create();
        Product::factory()->count(2)->inactive()->create();

        $activeProducts = Product::active()->get();

        $this->assertCount(3, $activeProducts);
        $activeProducts->each(function ($product) {
            $this->assertTrue($product->is_active);
        });
    }

    public function test_product_soft_deletes(): void
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);
        $this->assertNull(Product::find($productId));
        $this->assertNotNull(Product::withTrashed()->find($productId));
    }

    public function test_product_formatted_package_weight_accessor(): void
    {
        $product = Product::factory()->create(['package_weight' => 1.5]);

        $this->assertEquals('1.500 kg', $product->formatted_package_weight);
    }

    public function test_product_formatted_price_per_liter_accessor(): void
    {
        $product = Product::factory()->create(['price_per_liter' => 2.5]);

        $this->assertEquals('2.50 €/L', $product->formatted_price_per_liter);
    }

    public function test_product_get_category_attributes(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $attributes = $product->getCategoryAttributes();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $attributes);
    }

    public function test_product_fillable_attributes(): void
    {
        $product = new Product();
        $fillable = $product->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_product_casts(): void
    {
        $product = Product::factory()->create([
            'price' => '10.50',
            'is_active' => '1',
            'is_organic' => '0',
        ]);

        $this->assertIsFloat($product->price);
        $this->assertIsBool($product->is_active);
        $this->assertIsBool($product->is_organic);
    }

    public function test_product_translatable_fields(): void
    {
        $product = Product::factory()->create([
            'description' => [
                'en' => 'English description',
                'lt' => 'Lietuviškas aprašymas',
            ],
        ]);

        $this->assertEquals('English description', $product->getTranslation('description', 'en'));
        $this->assertEquals('Lietuviškas aprašymas', $product->getTranslation('description', 'lt'));
    }
}

