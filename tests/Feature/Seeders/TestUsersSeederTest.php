<?php

namespace Tests\Feature\Seeders;

use App\Models\Category;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Database\Seeders\test_information\TestUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_users_seeder_is_idempotent(): void
    {
        $mainCategory = Category::factory()->create();
        Category::factory()->count(6)->create([
            'parent_category_id' => $mainCategory->id,
        ]);

        $this->seed(TestUsersSeeder::class);

        $initialBuyerCount = Buyer::query()->count();
        $initialSellerCount = Seller::query()->count();
        $initialSellerCategoryCount = Seller::query()
            ->withCount('categories')
            ->get()
            ->sum('categories_count');

        $this->assertSame(10, $initialBuyerCount);
        $this->assertSame(10, $initialSellerCount);
        $this->assertSame(40, $initialSellerCategoryCount);

        $this->seed(TestUsersSeeder::class);

        $this->assertSame($initialBuyerCount, Buyer::query()->count());
        $this->assertSame($initialSellerCount, Seller::query()->count());
        $this->assertSame(
            $initialSellerCategoryCount,
            Seller::query()->withCount('categories')->get()->sum('categories_count')
        );
    }
}
