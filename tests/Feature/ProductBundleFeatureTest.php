<?php

namespace Tests\Feature;

use App\Actions\ProductBundles\CalculateBundlePriceAction;
use App\Actions\ProductBundles\ValidateBundleAvailabilityAction;
use App\Livewire\Backend\ProductBundles\Index as BackendProductBundleIndex;
use App\Livewire\Frontend\Seller\ProductBundles\Form as SellerProductBundleForm;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductBundleItem;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class ProductBundleFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_seller_can_create_bundle_with_own_products(): void
    {
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 10.00, 'stock' => 10]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 15.00, 'stock' => 10]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductBundleForm::class)
            ->set('name', 'Seller Starter Set')
            ->set('slug', 'seller-starter-set')
            ->set('status', ProductBundle::STATUS_DRAFT)
            ->set('selectedProductIds', [$firstProduct->id, $secondProduct->id])
            ->set('itemQuantities.'.$firstProduct->id, 2)
            ->set('itemQuantities.'.$secondProduct->id, 1)
            ->call('save')
            ->assertHasNoErrors();

        $bundle = ProductBundle::query()->where('slug', 'seller-starter-set')->firstOrFail();

        $this->assertSame($seller->id, $bundle->seller_id);
        $this->assertSame(2, $bundle->items()->count());
        $this->assertDatabaseHas('product_bundle_items', [
            'product_bundle_id' => $bundle->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);
    }

    public function test_seller_cannot_add_another_sellers_product_to_bundle(): void
    {
        $seller = $this->createSeller();
        $ownProduct = $this->createProduct(['seller_id' => $seller->id]);
        $foreignProduct = $this->createProduct();

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductBundleForm::class)
            ->set('name', 'Invalid Set')
            ->set('slug', 'invalid-set')
            ->set('selectedProductIds', [$ownProduct->id, $foreignProduct->id])
            ->call('save')
            ->assertHasErrors(['product_ids']);

        $this->assertDatabaseMissing('product_bundles', [
            'slug' => 'invalid-set',
        ]);
    }

    public function test_seller_cannot_publish_bundle_with_less_than_two_products(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $seller->id]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductBundleForm::class)
            ->set('name', 'Too Small Set')
            ->set('slug', 'too-small-set')
            ->set('status', ProductBundle::STATUS_ACTIVE)
            ->set('selectedProductIds', [$product->id])
            ->call('save')
            ->assertHasErrors(['items']);

        $this->assertDatabaseMissing('product_bundles', [
            'slug' => 'too-small-set',
            'status' => ProductBundle::STATUS_ACTIVE,
        ]);
    }

    public function test_seller_cannot_publish_bundle_with_inactive_product(): void
    {
        $seller = $this->createSeller();
        $activeProduct = $this->createProduct(['seller_id' => $seller->id, 'is_active' => true]);
        $inactiveProduct = $this->createProduct(['seller_id' => $seller->id, 'is_active' => false]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductBundleForm::class)
            ->set('name', 'Inactive Product Set')
            ->set('slug', 'inactive-product-set')
            ->set('status', ProductBundle::STATUS_ACTIVE)
            ->set('selectedProductIds', [$activeProduct->id, $inactiveProduct->id])
            ->call('save')
            ->assertHasErrors(['items']);

        $this->assertDatabaseMissing('product_bundles', [
            'slug' => 'inactive-product-set',
            'status' => ProductBundle::STATUS_ACTIVE,
        ]);
    }

    public function test_bundle_price_calculates_percentage_and_fixed_discounts(): void
    {
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 10.00, 'stock' => 10]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 25.00, 'stock' => 10]);
        $bundle = $this->createBundle($seller, [
            [$firstProduct, 2],
            [$secondProduct, 1],
        ], [
            'discount_type' => ProductBundle::DISCOUNT_TYPE_PERCENTAGE,
            'discount_value' => 20,
        ]);

        $price = app(CalculateBundlePriceAction::class)->handle($bundle);

        $this->assertSame(45.0, $price['base_price']);
        $this->assertSame(9.0, $price['discount_amount']);
        $this->assertSame(36.0, $price['final_price']);

        $bundle->forceFill([
            'discount_type' => ProductBundle::DISCOUNT_TYPE_FIXED_AMOUNT,
            'discount_value' => 5,
        ])->save();

        $fixedPrice = app(CalculateBundlePriceAction::class)->handle($bundle->refresh()->load('items.product'));

        $this->assertSame(45.0, $fixedPrice['base_price']);
        $this->assertSame(5.0, $fixedPrice['discount_amount']);
        $this->assertSame(40.0, $fixedPrice['final_price']);
    }

    public function test_fixed_discount_cannot_make_bundle_price_negative(): void
    {
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 10.00, 'stock' => 10]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id, 'price' => 5.00, 'stock' => 10]);
        $bundle = $this->createBundle($seller, [
            [$firstProduct, 1],
            [$secondProduct, 1],
        ], [
            'discount_type' => ProductBundle::DISCOUNT_TYPE_FIXED_AMOUNT,
            'discount_value' => 20,
        ]);

        $this->expectException(ValidationException::class);

        app(ValidateBundleAvailabilityAction::class)->validateForPublication($bundle);
    }

    public function test_only_active_visible_bundles_are_public(): void
    {
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id]);
        $activeBundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]]);
        $draftBundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]], [
            'slug' => 'draft-public-test-set',
            'status' => ProductBundle::STATUS_DRAFT,
            'published_at' => null,
        ]);
        $archivedBundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]], [
            'slug' => 'archived-public-test-set',
            'status' => ProductBundle::STATUS_ARCHIVED,
            'published_at' => null,
        ]);

        $this->get(route('buyer.bundles.show', $activeBundle))->assertOk();
        $this->get(route('buyer.bundles.show', $draftBundle))->assertNotFound();
        $this->get(route('buyer.bundles.show', $archivedBundle))->assertNotFound();
    }

    public function test_admin_can_moderate_bundle_status(): void
    {
        $admin = $this->createAdmin();
        $seller = $this->createSeller();
        $firstProduct = $this->createProduct(['seller_id' => $seller->id]);
        $secondProduct = $this->createProduct(['seller_id' => $seller->id]);
        $bundle = $this->createBundle($seller, [[$firstProduct, 1], [$secondProduct, 1]], [
            'status' => ProductBundle::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->actingAs($admin, 'admin');

        Livewire::test(BackendProductBundleIndex::class)
            ->call('publishBundle', $bundle->id)
            ->assertHasNoErrors();

        $this->assertSame(ProductBundle::STATUS_ACTIVE, $bundle->refresh()->status);
    }

    /**
     * @param  array<int, array{0: Product, 1: int}>  $products
     * @param  array<string, mixed>  $attributes
     */
    private function createBundle(Seller $seller, array $products, array $attributes = []): ProductBundle
    {
        $bundle = ProductBundle::factory()
            ->for($seller, 'seller')
            ->active()
            ->create(array_merge([
                'slug' => 'bundle-'.fake()->unique()->numberBetween(1000, 9999),
            ], $attributes));

        foreach ($products as $index => [$product, $quantity]) {
            ProductBundleItem::factory()
                ->forBundle($bundle, $product, $quantity)
                ->create([
                    'sort_order' => $index,
                ]);
        }

        return $bundle->refresh()->load('seller', 'items.product.seller');
    }
}
