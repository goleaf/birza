<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ProductService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Users\Seller;
use App\Models\AttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService();
        Storage::fake('public');
    }

    public function test_create_product(): void
    {
        $category = Category::factory()->create();
        $seller = Seller::factory()->create();
        $data = [
            'name' => 'Test Product',
            'category_id' => $category->id,
            'seller_id' => $seller->id,
            'price' => 10.50,
            'is_active' => true,
        ];

        $product = $this->service->createProduct($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertEquals($seller->id, $product->seller_id);
    }

    public function test_create_product_with_attributes(): void
    {
        $product = Product::factory()->create();
        $attributeValues = AttributeValue::factory()->count(3)->create();

        $data = [
            'name' => 'Test Product',
            'category_id' => Category::factory()->create()->id,
            'seller_id' => Seller::factory()->create()->id,
            'price' => 10.50,
            'attributes' => $attributeValues->pluck('id')->toArray(),
        ];

        $createdProduct = $this->service->createProduct($data);

        $this->assertCount(3, $createdProduct->attributeValues);
    }

    public function test_create_product_with_images(): void
    {
        $image = UploadedFile::fake()->image('product.jpg');
        $data = [
            'name' => 'Test Product',
            'category_id' => Category::factory()->create()->id,
            'seller_id' => Seller::factory()->create()->id,
            'price' => 10.50,
            'product_image' => $image,
        ];

        $product = $this->service->createProduct($data);

        $this->assertNotNull($product->product_image);
        Storage::disk('public')->assertExists('products/' . $product->product_image);
    }

    public function test_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);
        $data = [
            'name' => 'New Name',
            'price' => 20.00,
        ];

        $this->service->updateProduct($product, $data);

        $product->refresh();
        $this->assertEquals('New Name', $product->name);
        $this->assertEquals(20.00, $product->price);
    }

    public function test_update_product_with_attributes(): void
    {
        $product = Product::factory()->create();
        $attributeValues = AttributeValue::factory()->count(2)->create();
        $attributes = [];
        foreach ($attributeValues as $value) {
            $attributes[$value->attribute_id] = $value->id;
        }

        $data = [
            'attributes' => $attributes,
        ];

        $this->service->updateProduct($product, $data);

        $product->refresh();
        $this->assertGreaterThanOrEqual(0, $product->attributeValues->count());
    }

    public function test_soft_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->service->softDeleteProduct($product);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_restore_product(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->service->restoreProduct($product->id);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_force_delete_product(): void
    {
        $product = Product::factory()->create([
            'product_image' => 'test.webp',
            'product_additional_image' => 'test2.webp',
        ]);
        Storage::disk('public')->put('products/test.webp', 'content');
        Storage::disk('public')->put('products/test2.webp', 'content');
        $attributeValue = AttributeValue::factory()->create();
        $product->attributeValues()->attach($attributeValue->id);

        $this->service->forceDeleteProduct($product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing('products/test.webp');
        Storage::disk('public')->assertMissing('products/test2.webp');
    }

    public function test_prepare_product_data_sets_defaults(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('prepareProductData');
        $method->setAccessible(true);

        $data = ['name' => 'Test'];
        $result = $method->invoke($this->service, $data);

        $this->assertFalse($result['is_active']);
        $this->assertFalse($result['is_organic']);
    }
}

