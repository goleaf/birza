<?php

namespace Tests\Feature\Seeders;

use App\Models\Category;
use Database\Seeders\test_information\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_seeder_is_idempotent(): void
    {
        $this->seed(CategorySeeder::class);

        $initialCategoryCount = Category::query()->count();
        $initialMainCategoryCount = Category::query()->whereNull('parent_category_id')->count();
        $initialSubcategoryCount = Category::query()->whereNotNull('parent_category_id')->count();

        $this->assertGreaterThan(0, $initialMainCategoryCount);
        $this->assertGreaterThan(0, $initialSubcategoryCount);
        $this->assertSame($initialCategoryCount, $initialMainCategoryCount + $initialSubcategoryCount);

        $this->seed(CategorySeeder::class);

        $this->assertSame($initialCategoryCount, Category::query()->count());
        $this->assertSame($initialMainCategoryCount, Category::query()->whereNull('parent_category_id')->count());
        $this->assertSame($initialSubcategoryCount, Category::query()->whereNotNull('parent_category_id')->count());
    }
}
