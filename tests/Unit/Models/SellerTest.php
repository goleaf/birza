<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Users\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SellerTransaction;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_has_many_products(): void
    {
        $seller = Seller::factory()->create();
        Product::factory()->count(3)->create(['seller_id' => $seller->id]);

        $this->assertCount(3, $seller->products);
    }

    public function test_seller_has_many_transactions(): void
    {
        $seller = Seller::factory()->create();
        SellerTransaction::factory()->count(3)->create(['seller_id' => $seller->id]);

        $this->assertCount(3, $seller->transactions);
    }

    public function test_seller_belongs_to_many_categories(): void
    {
        $seller = Seller::factory()->create();
        $categories = Category::factory()->count(3)->create();

        $seller->categories()->attach($categories->pluck('id'));

        $this->assertCount(3, $seller->categories);
    }

    public function test_seller_active_state(): void
    {
        $activeSeller = Seller::factory()->active()->create();
        $inactiveSeller = Seller::factory()->inactive()->create();

        $this->assertTrue($activeSeller->is_active);
        $this->assertFalse($inactiveSeller->is_active);
    }

    public function test_seller_soft_deletes(): void
    {
        $seller = Seller::factory()->create();
        $sellerId = $seller->id;

        $seller->delete();

        $this->assertSoftDeleted('users_sellers', ['id' => $sellerId]);
    }

    public function test_seller_password_is_hashed(): void
    {
        $seller = Seller::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $seller->password);
        $this->assertTrue(\Hash::check('plaintext', $seller->password));
    }
}

