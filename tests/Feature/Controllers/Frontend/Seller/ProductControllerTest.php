<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use App\Livewire\Frontend\Seller\Products\Index as SellerProductsIndex;
use App\Models\Category;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_requires_authentication(): void
    {
        $response = $this->get(route('seller.products.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_product_index_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        $category = Category::factory()->create([
            'category_name' => ['en' => 'Vegetables', 'lt' => 'Darzoves'],
        ]);
        $subcategory = Category::factory()->create([
            'parent_category_id' => $category->id,
            'category_name' => ['en' => 'Root Vegetables', 'lt' => 'Sakninės daržovės'],
        ]);
        $seller->categories()->attach($subcategory);

        Product::factory()
            ->count(3)
            ->sequence(
                ['name' => 'Seller Product One'],
                ['name' => 'Seller Product Two'],
                ['name' => 'Seller Product Three'],
            )
            ->create([
                'category_id' => $subcategory->id,
                'seller_id' => $seller->id,
                'is_active' => true,
                'is_organic' => true,
            ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.products.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerProductsIndex::class)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_products'))
            ->assertSee(__('product_products_list'))
            ->assertSee(__('common_back_to_dashboard'))
            ->assertSee($category->getTranslation('category_name', app()->getLocale()))
            ->assertSee($subcategory->getTranslation('category_name', app()->getLocale()))
            ->assertSee('collapse-title', false)
            ->assertSee(__('product_products_list'))
            ->assertSee('Seller Product One')
            ->assertSee(__('common_yes'));
    }

    public function test_product_create_form_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();
        $category = Category::factory()->create([
            'category_name' => ['en' => 'Dairy', 'lt' => 'Pieno produktai'],
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.products.create', ['categoryId' => $category->id]));

        $response->assertStatus(200)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('common_products'))
            ->assertSee(__('common_create'))
            ->assertSee(__('product_create_new_product'))
            ->assertSee('easymde.min.css')
            ->assertSee('new EasyMDE', false)
            ->assertSee('flatpickr.min.css')
            ->assertSee('flatpickr($refs.input', false)
            ->assertSee(__('units_unit_kg'))
            ->assertSee(__('common_back_to_products'));
    }

    public function test_product_edit_form_displays_gallery_for_existing_images(): void
    {
        Storage::fake('public');

        $seller = Seller::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'product_image' => 'primary.webp',
            'product_additional_image' => 'secondary.webp',
            'unit' => 'pack',
        ]);

        Storage::disk('public')->put('products/primary.webp', 'primary');
        Storage::disk('public')->put('products/secondary.webp', 'secondary');

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.products.edit', $product));

        $response->assertStatus(200)
            ->assertSee(__('product_edit_product'))
            ->assertSee(__('common_product_images'))
            ->assertSee(__('backend_products_image_library_hint'))
            ->assertSee(__('units_unit_pack'));
    }

    public function test_product_soft_delete_confirmation_uses_modal_flow(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'is_active' => true,
        ]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductsIndex::class)
            ->call('confirmSoftDeleteProduct', $product->id)
            ->assertSet('confirmModal', true)
            ->assertSet('confirmModalMethod', 'softDeleteProduct')
            ->call('runConfirmedAction')
            ->assertSet('confirmModal', false);

        $product->refresh();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertFalse($product->is_active);
    }
}
