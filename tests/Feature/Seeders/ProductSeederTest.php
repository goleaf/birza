<?php

namespace Tests\Feature\Seeders;

use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\Users\Seller;
use Database\Seeders\test_information\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_seeder_is_idempotent(): void
    {
        config([
            'filesystems.default' => 'local',
        ]);

        Storage::fake('local');

        Seller::factory()->count(3)->create();
        Country::factory()->count(2)->create([
            'region' => 'Europe',
        ]);

        $mainCategory = Category::factory()->create();
        Category::factory()->count(2)->create([
            'parent_category_id' => $mainCategory->id,
        ]);

        $this->seed(ProductSeeder::class);

        $initialProductCount = Product::query()->count();

        $this->assertSame(20, $initialProductCount);

        $this->seed(ProductSeeder::class);

        $this->assertSame($initialProductCount, Product::query()->count());
    }
}
