<?php

namespace Tests\Feature\Products;

use App\Actions\Products\Comparison\AddProductToCompareAction;
use App\Actions\Products\Comparison\ClearProductCompareAction;
use App\Actions\Products\Comparison\RemoveProductFromCompareAction;
use App\Livewire\Frontend\Buyer\Products\Index as BuyerProductsIndex;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Review;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ProductComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        session()->forget('product_compare.ids');
    }

    public function test_guest_can_add_active_product_to_comparison(): void
    {
        $product = $this->createComparableProduct();

        Livewire::test(BuyerProductsIndex::class)
            ->call('addToCompare', $product->id);

        $this->assertSame([$product->id], session('product_compare.ids'));
    }

    public function test_buyer_can_add_active_product_to_comparison(): void
    {
        $buyer = Buyer::factory()->active()->verified()->create();
        $product = $this->createComparableProduct();

        $this->actingAs($buyer, 'buyer');

        Livewire::test(BuyerProductsIndex::class)
            ->call('addToCompare', $product->id);

        $this->assertSame([$product->id], session('product_compare.ids'));
    }

    public function test_duplicate_product_is_not_added_twice(): void
    {
        $product = $this->createComparableProduct();
        $action = app(AddProductToCompareAction::class);

        $action->handle($product);

        try {
            $action->handle($product);
        } catch (ValidationException $exception) {
            $this->assertSame(__('compare.messages.already_exists'), collect($exception->errors())->flatten()->first());
        }

        $this->assertSame([$product->id], session('product_compare.ids'));
    }

    public function test_comparison_has_maximum_limit(): void
    {
        $products = Product::factory()
            ->count(5)
            ->active()
            ->create($this->baseProductAttributes());
        $action = app(AddProductToCompareAction::class);

        $products->take(4)->each(function (Product $product) use ($action): void {
            $action->handle($product);
        });

        $this->expectException(ValidationException::class);
        $action->handle($products->last());
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $product = $this->createComparableProduct(['is_active' => false]);

        $this->expectException(ValidationException::class);

        app(AddProductToCompareAction::class)->handle($product);
    }

    public function test_deleted_product_cannot_be_added(): void
    {
        $product = $this->createComparableProduct();
        $product->delete();

        $this->expectException(ValidationException::class);

        app(AddProductToCompareAction::class)->handle($product);
    }

    public function test_product_can_be_removed(): void
    {
        $product = $this->createComparableProduct();
        session()->put('product_compare.ids', [$product->id]);

        app(RemoveProductFromCompareAction::class)->handle($product);

        $this->assertSame([], session('product_compare.ids'));
    }

    public function test_comparison_can_be_cleared(): void
    {
        $products = Product::factory()
            ->count(2)
            ->active()
            ->create($this->baseProductAttributes());
        session()->put('product_compare.ids', $products->modelKeys());

        app(ClearProductCompareAction::class)->handle();

        $this->assertFalse(session()->has('product_compare.ids'));
    }

    public function test_comparison_page_shows_compared_products_without_private_fields(): void
    {
        $seller = Seller::factory()->active()->verified()->create([
            'company_name' => 'Public Seller Company',
            'email' => 'private-seller@example.test',
            'bank_account' => 'LT123456789',
        ]);
        $product = $this->createComparableProduct([
            'name' => 'Compare Visible Yogurt',
            'seller_id' => $seller->id,
            'price' => 7.25,
            'stock' => 12,
        ]);

        Review::factory()->approved()->for($product)->create([
            'rating' => 5,
        ]);

        $response = $this->withSession(['product_compare.ids' => [$product->id]])
            ->get(route('buyer.compare.index'));

        $response->assertOk()
            ->assertSee(__('compare.title'))
            ->assertSee('Compare Visible Yogurt')
            ->assertSee('Public Seller Company')
            ->assertSee(__('compare.availability.in_stock'))
            ->assertSee('5.0 / 5')
            ->assertDontSee('private-seller@example.test')
            ->assertDontSee('LT123456789');
    }

    public function test_comparison_page_removes_product_that_becomes_unavailable(): void
    {
        $activeProduct = $this->createComparableProduct(['name' => 'Still Available']);
        $inactiveProduct = $this->createComparableProduct([
            'name' => 'Unavailable Product',
            'is_active' => false,
        ]);

        $response = $this->withSession([
            'product_compare.ids' => [$activeProduct->id, $inactiveProduct->id],
        ])->get(route('buyer.compare.index'));

        $response->assertOk()
            ->assertSee('Still Available')
            ->assertDontSee('Unavailable Product');

        $this->assertSame([$activeProduct->id], session('product_compare.ids'));
    }

    public function test_comparison_translation_keys_exist(): void
    {
        $requiredKeys = [
            'compare.title',
            'compare.empty',
            'compare.actions.add',
            'compare.actions.remove',
            'compare.actions.clear',
            'compare.messages.added',
            'compare.messages.removed',
            'compare.messages.cleared',
            'compare.messages.limit_reached',
            'compare.messages.already_exists',
            'compare.messages.product_unavailable',
        ];

        foreach ((array) config('app.locales') as $locale) {
            $translations = json_decode((string) file_get_contents(lang_path("{$locale}.json")), true, 512, JSON_THROW_ON_ERROR);

            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $translations, "Missing [{$key}] in [{$locale}].");
            }
        }
    }

    public function test_comparison_page_has_mobile_friendly_structure(): void
    {
        $product = $this->createComparableProduct();

        $response = $this->withSession(['product_compare.ids' => [$product->id]])
            ->get(route('buyer.compare.index'));

        $response->assertOk()
            ->assertSee('grid grid-cols-1 gap-4 md:grid-cols-2 lg:hidden', false)
            ->assertSee('data-testid="compare-scroll-container"', false)
            ->assertSee('overflow-x-auto', false);
    }

    public function test_guest_catalog_and_product_detail_show_compare_controls(): void
    {
        $product = $this->createComparableProduct([
            'name' => 'Guest Compare Product',
        ]);

        $this->get(route('buyer.products.index'))
            ->assertOk()
            ->assertSee('Guest Compare Product')
            ->assertSee(route('buyer.compare.index'))
            ->assertSee('wire:click="addToCompare', false);

        $this->get(route('buyer.products.show', $product))
            ->assertOk()
            ->assertSee('Guest Compare Product')
            ->assertSee(route('buyer.compare.index'))
            ->assertSee('wire:click="addToCompare"', false);
    }

    private function createComparableProduct(array $attributes = []): Product
    {
        return Product::factory()
            ->active()
            ->create(array_merge($this->baseProductAttributes(), $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function baseProductAttributes(): array
    {
        $parentCategory = Category::factory()->active()->create([
            'category_name' => ['en' => 'Food', 'lt' => 'Maistas'],
        ]);
        $category = Category::factory()->active()->create([
            'parent_category_id' => $parentCategory->id,
            'category_name' => ['en' => 'Dairy', 'lt' => 'Pienas'],
        ]);
        $country = Country::query()->firstOrCreate(
            ['alpha2' => 'LT'],
            [
                'region' => 'Europe',
                'is_active' => true,
                'country_name' => ['en' => 'Lithuania', 'lt' => 'Lietuva'],
                'description' => [
                    'en' => 'Lithuanian comparison test origin.',
                    'lt' => 'Lietuvos palyginimo testo kilmes salis.',
                ],
            ],
        );
        $seller = Seller::factory()->active()->verified()->create([
            'company_name' => 'Comparison Seller',
        ]);

        return [
            'category_id' => $category->id,
            'country_of_origin' => $country->id,
            'seller_id' => $seller->id,
            'price' => 10.00,
            'min_order_count' => 1,
            'stock' => 10,
            'unit' => 'kg',
            'product_image' => '',
            'product_additional_image' => null,
            'description' => [
                'en' => 'A public product description for comparison.',
                'lt' => 'Viesas prekes aprasymas palyginimui.',
            ],
        ];
    }
}
