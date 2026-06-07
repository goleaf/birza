<?php

namespace Tests\Feature\Marketplace;

use App\Livewire\Frontend\Seller\Products\Index as SellerProductsIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class RoleAccessFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_guest_cannot_access_role_dashboards(): void
    {
        $this->get(route('buyer.dashboard'))->assertRedirect(route('home'));
        $this->get(route('seller.dashboard'))->assertRedirect(route('home'));
        $this->get(route('backend.dashboard'))->assertRedirect(route('home'));
    }

    public function test_buyer_can_access_buyer_dashboard_only(): void
    {
        $buyer = $this->createBuyer();

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.dashboard'))
            ->assertOk();

        $this->actingAs($buyer, 'buyer')
            ->get(route('seller.dashboard'))
            ->assertRedirect(route('home'));

        $this->actingAs($buyer, 'buyer')
            ->get(route('backend.dashboard'))
            ->assertRedirect(route('home'));
    }

    public function test_seller_can_access_seller_dashboard_only(): void
    {
        $seller = $this->createSeller();

        $this->actingAs($seller, 'seller')
            ->get(route('seller.dashboard'))
            ->assertOk();

        $this->actingAs($seller, 'seller')
            ->get(route('buyer.dashboard'))
            ->assertRedirect(route('home'));

        $this->actingAs($seller, 'seller')
            ->get(route('backend.dashboard'))
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('backend.dashboard'))
            ->assertOk();
    }

    public function test_normal_user_cannot_access_admin_dashboard(): void
    {
        $normalUser = User::factory()->create();

        $this->actingAs($normalUser)
            ->get(route('backend.dashboard'))
            ->assertRedirect(route('home'));
    }

    public function test_buyer_cannot_view_another_buyer_order(): void
    {
        $owner = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $order = $this->createOrderWithItem($owner);

        $this->actingAs($otherBuyer, 'buyer')
            ->get(route('buyer.orders.show', $order))
            ->assertForbidden();
    }

    public function test_seller_cannot_view_order_without_own_items(): void
    {
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $order = $this->createOrderWithItem(seller: $otherSeller);

        $this->actingAs($seller, 'seller')
            ->get(route('seller.orders.show', $order))
            ->assertForbidden();
    }

    public function test_seller_cannot_edit_or_delete_another_sellers_product(): void
    {
        $seller = $this->createSeller();
        $otherSeller = $this->createSeller();
        $product = $this->createProduct(['seller_id' => $otherSeller->id]);

        $this->actingAs($seller, 'seller')
            ->get(route('seller.products.edit', $product))
            ->assertForbidden();

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductsIndex::class)
            ->call('softDeleteProduct', $product->id)
            ->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }
}
