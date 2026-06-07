<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Livewire\Frontend\Buyer\Products\Index as BuyerProductsIndex;
use App\Livewire\Frontend\Buyer\Products\Show as BuyerProductsShow;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_requires_authentication(): void
    {
        Product::factory()->active()->count(5)->create();

        $response = $this->get(route('buyer.products.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_product_index_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        $category = Category::factory()->create([
            'category_name' => ['en' => 'Vegetables', 'lt' => 'Darzoves'],
        ]);
        $subcategory = Category::factory()->create([
            'parent_category_id' => $category->id,
            'category_name' => ['en' => 'Root Vegetables', 'lt' => 'Sakninės daržovės'],
        ]);

        Product::factory()->active()->count(5)->create([
            'category_id' => $subcategory->id,
        ]);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerProductsIndex::class)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_products'))
            ->assertSee($category->getTranslation('category_name', app()->getLocale()))
            ->assertSee($subcategory->getTranslation('category_name', app()->getLocale()))
            ->assertSee('collapse-title', false);
    }

    public function test_product_index_filters_by_selected_attribute_value_without_lazy_loading(): void
    {
        $buyer = Buyer::factory()->create();
        $parentCategory = Category::factory()->create();
        $category = Category::factory()->create([
            'parent_category_id' => $parentCategory->id,
        ]);
        $attribute = Attribute::factory()->create([
            'is_active' => true,
            'is_filterable' => true,
        ]);
        $attribute->categories()->attach($category);

        $matchingValue = AttributeValue::factory()->for($attribute)->create();
        $otherValue = AttributeValue::factory()->for($attribute)->create();
        $matchingProduct = Product::factory()->active()->create([
            'name' => 'Matching product',
            'category_id' => $category->id,
        ]);
        $otherProduct = Product::factory()->active()->create([
            'name' => 'Other product',
            'category_id' => $category->id,
        ]);

        $matchingProduct->attributeValues()->attach($matchingValue, [
            'attribute_id' => $attribute->id,
        ]);
        $otherProduct->attributeValues()->attach($otherValue, [
            'attribute_id' => $attribute->id,
        ]);

        Model::preventLazyLoading();
        DB::enableQueryLog();

        try {
            $response = $this->actingAs($buyer, 'buyer')->get(route('buyer.products.index', [
                'category' => $category->id,
                'filters' => [$attribute->id => $matchingValue->id],
            ]));

            $attributeQueries = collect(DB::getQueryLog())
                ->pluck('query')
                ->filter(fn (string $query): bool => str_contains($query, 'from "attributes" inner join "category_attribute"'));

            $response->assertOk()
                ->assertSee('Matching product')
                ->assertDontSee('Other product');
            $this->assertCount(1, $attributeQueries);
        } finally {
            DB::disableQueryLog();
            Model::preventLazyLoading(false);
        }
    }

    public function test_product_index_filters_by_stock_range(): void
    {
        $buyer = Buyer::factory()->create();
        $parentCategory = Category::factory()->create();
        $category = Category::factory()->create([
            'parent_category_id' => $parentCategory->id,
        ]);

        Product::factory()->active()->create([
            'name' => 'In stock range',
            'category_id' => $category->id,
            'stock' => 25,
        ]);
        Product::factory()->active()->create([
            'name' => 'Below stock range',
            'category_id' => $category->id,
            'stock' => 5,
        ]);
        Product::factory()->active()->create([
            'name' => 'Above stock range',
            'category_id' => $category->id,
            'stock' => 75,
        ]);

        $response = $this->actingAs($buyer, 'buyer')->get(route('buyer.products.index', [
            'stock_min' => 10,
            'stock_max' => 50,
        ]));

        $response->assertOk()
            ->assertSee('In stock range')
            ->assertDontSee('Below stock range')
            ->assertDontSee('Above stock range');
    }

    public function test_product_show_requires_authentication(): void
    {
        $product = Product::factory()->active()->create();

        $response = $this->get(route('buyer.products.show', $product));

        $response->assertRedirect(route('home'));
    }

    public function test_product_show_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        $seller = Seller::factory()->create([
            'name' => 'Seller Jane',
            'company_name' => 'Fresh Farm',
            'email' => 'seller@example.com',
        ]);
        $product = Product::factory()->active()->create([
            'seller_id' => $seller->id,
            'product_image' => 'primary.webp',
            'product_additional_image' => 'secondary.webp',
            'description' => [
                'en' => "# Fresh Farm\n\n- Organic\n- Local",
                'lt' => "# Fresh Farm\n\n- Organic\n- Local",
            ],
        ]);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.show', $product));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerProductsShow::class)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_products'))
            ->assertSee($product->name)
            ->assertSee('seller@example.com')
            ->assertSee('photoswipe.umd.min.js')
            ->assertSee('PhotoSwipeLightbox', false)
            ->assertSee('pswp-gallery', false)
            ->assertSee((string) config('images.fallbacks.product'))
            ->assertSee('<h1>Fresh Farm</h1>', false)
            ->assertSee('<li>Organic</li>', false);
    }

    public function test_product_show_eager_loads_all_relationships_used_by_the_view(): void
    {
        $buyer = Buyer::factory()->create();
        $parentCategory = Category::factory()->create();
        $category = Category::factory()->create([
            'parent_category_id' => $parentCategory->id,
        ]);
        $attribute = Attribute::factory()->create([
            'is_active' => true,
        ]);
        $attribute->categories()->attach($category);
        $attributeValue = AttributeValue::factory()->for($attribute)->create([
            'value' => [
                'en' => 'Organic',
                'lt' => 'Organic',
            ],
        ]);
        $product = Product::factory()->active()->create([
            'category_id' => $category->id,
        ]);
        $product->attributeValues()->attach($attributeValue, [
            'attribute_id' => $attribute->id,
        ]);

        $component = app(BuyerProductsShow::class);
        $component->mount($product);

        $this->assertTrue($component->product->relationLoaded('seller'));
        $this->assertTrue($component->product->relationLoaded('country'));
        $this->assertTrue($component->product->relationLoaded('category'));
        $this->assertTrue($component->product->category->relationLoaded('parent'));
        $this->assertTrue($component->product->category->relationLoaded('attributes'));
        $this->assertTrue($component->product->relationLoaded('attributeValues'));
        $this->assertSame('Organic', $component->product->attributeValues->sole()->value);
    }
}
