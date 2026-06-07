<?php

namespace Tests\Feature\Marketplace;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class ProductCatalogFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_buyer_catalog_shows_active_products_and_hides_inactive_or_deleted_products(): void
    {
        $buyer = $this->createBuyer();
        $activeProduct = $this->createProduct(['name' => 'Visible Apples']);
        $inactiveProduct = $this->createProduct([
            'name' => 'Hidden Draft Apples',
            'is_active' => false,
        ]);
        $deletedProduct = $this->createProduct(['name' => 'Deleted Apples']);
        $deletedProduct->delete();

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.index'));

        $response->assertOk()
            ->assertSee($activeProduct->name)
            ->assertDontSee($inactiveProduct->name)
            ->assertDontSee($deletedProduct->name);
    }

    public function test_buyer_catalog_category_and_price_filters_work(): void
    {
        $buyer = $this->createBuyer();
        $matchingProduct = $this->createProduct([
            'name' => 'Filtered Cheese',
            'price' => 25.00,
        ]);
        $outsidePrice = $this->createProduct([
            'name' => 'Expensive Cheese',
            'category_id' => $matchingProduct->category_id,
            'price' => 75.00,
        ]);
        $outsideCategory = $this->createProduct([
            'name' => 'Filtered Wrong Category',
            'price' => 25.00,
        ]);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.index', [
                'category' => $matchingProduct->category_id,
                'price_min' => 20,
                'price_max' => 30,
            ]));

        $response->assertOk()
            ->assertSee($matchingProduct->name)
            ->assertDontSee($outsidePrice->name)
            ->assertDontSee($outsideCategory->name);
    }

    public function test_buyer_catalog_empty_state_appears_when_no_products_match(): void
    {
        $buyer = $this->createBuyer();
        $this->createProduct([
            'name' => 'Too Cheap',
            'price' => 5.00,
        ]);

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.index', [
                'price_min' => 500,
                'price_max' => 600,
            ]))
            ->assertOk()
            ->assertSee(__('product_no_products_found'));
    }

    public function test_active_product_detail_opens_and_inactive_or_deleted_products_are_hidden(): void
    {
        $buyer = $this->createBuyer();
        $activeProduct = $this->createProduct(['name' => 'Detail Milk']);
        $inactiveProduct = $this->createProduct([
            'name' => 'Inactive Detail Milk',
            'is_active' => false,
        ]);
        $deletedProduct = $this->createProduct(['name' => 'Deleted Detail Milk']);
        $deletedProduct->delete();

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.show', $activeProduct))
            ->assertOk()
            ->assertSee($activeProduct->name);

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.show', $inactiveProduct))
            ->assertNotFound();

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.products.show', $deletedProduct))
            ->assertNotFound();
    }
}
