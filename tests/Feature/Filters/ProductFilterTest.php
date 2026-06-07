<?php

namespace Tests\Feature\Filters;

use App\Http\Filters\ProductFilter;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_applies_allowlisted_catalog_filters(): void
    {
        $category = Category::factory()->create();
        $seller = Seller::factory()->create();
        $country = Country::factory()->create();
        $matchingProduct = Product::factory()->create([
            'category_id' => $category->id,
            'seller_id' => $seller->id,
            'country_of_origin' => $country->id,
            'price' => 25,
            'stock' => 20,
            'is_organic' => false,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'seller_id' => $seller->id,
            'country_of_origin' => $country->id,
            'price' => 50,
            'stock' => 5,
            'is_organic' => true,
        ]);

        $products = Product::query()
            ->filter(ProductFilter::fromArray([
                'category_id' => $category->id,
                'seller_id' => $seller->id,
                'country_of_origin' => $country->id,
                'min_price' => 20,
                'max_price' => 30,
                'min_stock' => 10,
                'max_stock' => 30,
                'is_organic' => false,
            ]))
            ->get();

        $this->assertTrue($products->contains($matchingProduct));
        $this->assertCount(1, $products);
    }

    public function test_it_filters_search_and_soft_delete_status(): void
    {
        $seller = Seller::factory()->create([
            'company_name' => 'Baltic Orchard',
        ]);
        $matchingProduct = Product::factory()->create([
            'seller_id' => $seller->id,
        ]);
        $matchingProduct->delete();
        Product::factory()->create();

        $products = Product::query()
            ->filter(ProductFilter::fromArray([
                'search' => 'Baltic Orchard',
                'status' => 'trashed',
            ]))
            ->get();

        $this->assertTrue($products->contains($matchingProduct));
        $this->assertCount(1, $products);
    }

    public function test_it_requires_every_selected_attribute_value(): void
    {
        $firstAttribute = Attribute::factory()->create();
        $secondAttribute = Attribute::factory()->create();
        $firstValue = AttributeValue::factory()->for($firstAttribute)->create();
        $secondValue = AttributeValue::factory()->for($secondAttribute)->create();
        $matchingProduct = Product::factory()->create();
        $partialProduct = Product::factory()->create();

        $matchingProduct->attributeValues()->attach($firstValue, ['attribute_id' => $firstAttribute->id]);
        $matchingProduct->attributeValues()->attach($secondValue, ['attribute_id' => $secondAttribute->id]);
        $partialProduct->attributeValues()->attach($firstValue, ['attribute_id' => $firstAttribute->id]);

        $products = Product::query()
            ->filter(ProductFilter::fromArray([
                'attribute_values' => [
                    $firstAttribute->id => $firstValue->id,
                    $secondAttribute->id => $secondValue->id,
                ],
            ]))
            ->get();

        $this->assertTrue($products->contains($matchingProduct));
        $this->assertFalse($products->contains($partialProduct));
        $this->assertCount(1, $products);
    }
}
