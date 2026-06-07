<?php

namespace Tests\Unit\Policies;

use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_manage_only_own_product(): void
    {
        $seller = Seller::factory()->verified()->active()->create();
        $otherSeller = Seller::factory()->verified()->active()->create();
        $ownProduct = Product::factory()->for($seller, 'seller')->create();
        $otherProduct = Product::factory()->for($otherSeller, 'seller')->create();

        $this->assertTrue($seller->can('update', $ownProduct));
        $this->assertTrue($seller->can('delete', $ownProduct));
        $this->assertTrue($seller->can('manageGallery', $ownProduct));
        $this->assertFalse($seller->can('update', $otherProduct));
        $this->assertFalse($seller->can('delete', $otherProduct));
        $this->assertFalse($seller->can('manageGallery', $otherProduct));
    }

    public function test_unverified_seller_cannot_create_or_publish_products(): void
    {
        $seller = Seller::factory()->unverified()->active()->create();
        $product = Product::factory()->for($seller, 'seller')->create();

        $this->assertFalse($seller->can('create', Product::class));
        $this->assertFalse($seller->can('publish', $product));
    }

    public function test_buyer_can_view_active_products_but_cannot_manage_them(): void
    {
        $buyer = Buyer::factory()->verified()->active()->create();
        $product = Product::factory()->active()->create();

        $this->assertTrue($buyer->can('view', $product));
        $this->assertFalse($buyer->can('update', $product));
        $this->assertFalse($buyer->can('delete', $product));
    }

    public function test_admin_can_moderate_and_force_delete_products(): void
    {
        $admin = Admin::factory()->active()->create();
        $product = Product::factory()->create();

        $this->assertTrue($admin->can('approve', $product));
        $this->assertTrue($admin->can('reject', $product));
        $this->assertTrue($admin->can('forceDelete', $product));
    }
}
