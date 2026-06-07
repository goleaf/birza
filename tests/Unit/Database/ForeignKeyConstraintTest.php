<?php

namespace Tests\Unit\Database;

use App\Models\BuyerCreditHistory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForeignKeyConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_credit_history_blocks_hard_deleting_referenced_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        BuyerCreditHistory::factory()->create(['buyer_id' => $buyer->id]);

        $this->expectException(QueryException::class);

        $buyer->forceDelete();
    }

    public function test_buyer_credit_history_keeps_history_when_admin_is_deleted(): void
    {
        $admin = Admin::factory()->create();
        $history = BuyerCreditHistory::factory()->create(['admin_id' => $admin->id]);

        $admin->delete();

        $this->assertDatabaseHas('buyer_credit_history', [
            'id' => $history->id,
            'admin_id' => null,
        ]);
    }

    public function test_category_children_survive_parent_hard_delete(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_category_id' => $parent->id]);

        $parent->delete();

        $this->assertDatabaseHas('categories', [
            'id' => $child->id,
            'parent_category_id' => null,
        ]);
    }

    public function test_reviews_keep_history_when_product_or_user_is_hard_deleted(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $product->forceDelete();
        $user->delete();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'product_id' => null,
            'user_id' => null,
        ]);
    }
}
