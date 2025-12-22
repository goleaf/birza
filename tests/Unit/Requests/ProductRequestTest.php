<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Users\Seller;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class ProductRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_request_validation_rules(): void
    {
        $request = new ProductRequest();
        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('category_id', $rules);
        $this->assertArrayHasKey('seller_id', $rules);
        $this->assertArrayHasKey('price', $rules);
    }

    public function test_product_request_validates_required_fields(): void
    {
        $request = new ProductRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    public function test_product_request_validates_category_exists(): void
    {
        $category = Category::factory()->create();
        $seller = Seller::factory()->create();
        $country = Country::factory()->create();

        $request = new ProductRequest();
        $rules = $request->rules();

        $data = [
            'name' => 'Test Product',
            'category_id' => $category->id,
            'seller_id' => $seller->id,
            'price' => 10.50,
            'country_of_origin' => $country->id,
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->fails());
    }
}

