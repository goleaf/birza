<?php

namespace Tests\Feature\Marketplace;

use App\Livewire\Frontend\Seller\Products\Create as SellerProductCreate;
use App\Livewire\Frontend\Seller\Products\Edit as SellerProductEdit;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class ImageUploadFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_seller_can_create_product_with_valid_uploaded_image(): void
    {
        Storage::fake('public');

        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
        ]);
        $category = $product->category;
        $country = $product->country;

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductCreate::class, ['categoryId' => $category])
            ->set('name', 'Seller Image Product')
            ->set('price', 12.50)
            ->set('pack_type', 'box')
            ->set('unit', 'kg')
            ->set('country_of_origin', $country->id)
            ->set('is_organic', 1)
            ->set('is_active', 1)
            ->set('min_order_price', 0)
            ->set('min_order_count', 1)
            ->set('stock', 6)
            ->set('total_shelf_life', 30)
            ->set('description.en', 'Fresh product with an uploaded image.')
            ->set('description.lt', 'Produktas su ikelta nuotrauka.')
            ->set('imageFiles.*', [
                UploadedFile::fake()->image('product.png', 40, 40),
            ])
            ->call('refreshMediaSources', 'imageFiles', 'imageLibrary')
            ->call('save')
            ->assertRedirect(route('seller.products.index'));

        $createdProduct = Product::query()
            ->where('seller_id', $seller->id)
            ->where('name', 'Seller Image Product')
            ->firstOrFail();

        $createdProduct->load('images');

        $this->assertCount(1, $createdProduct->images);
        $this->assertNotEmpty($createdProduct->product_image);
        Storage::disk('public')->assertExists($createdProduct->images->first()->storedPaths());
    }

    public function test_too_large_product_image_is_rejected_in_seller_form(): void
    {
        Storage::fake('public');

        $seller = $this->createSeller();
        $product = $this->createProduct([
            'seller_id' => $seller->id,
        ]);

        ProductImage::factory()->for($product)->create([
            'is_primary' => true,
            'path' => 'images/products/'.$product->id.'/medium/existing.webp',
        ]);

        $this->actingAs($seller, 'seller');

        Livewire::test(SellerProductEdit::class, ['product' => $product])
            ->set('imageFiles.*', [
                UploadedFile::fake()
                    ->image('too-large.png', 40, 40)
                    ->size(((int) config('images.types.product.max_kb')) + 1),
            ])
            ->call('refreshMediaSources', 'imageFiles', 'imageLibrary')
            ->assertHasErrors(['imageFiles.*']);

        $this->assertDatabaseCount('product_images', 1);
    }

    public function test_product_uses_fallback_when_image_record_points_to_missing_file(): void
    {
        Storage::fake('public');

        $product = $this->createProduct([
            'product_image' => '',
            'product_additional_image' => null,
        ]);

        ProductImage::factory()->for($product)->create([
            'is_primary' => true,
            'path' => 'images/products/'.$product->id.'/medium/missing.webp',
            'variants' => [
                'thumb' => ['path' => 'images/products/'.$product->id.'/thumb/missing.webp'],
            ],
        ]);

        $product->refresh()->load('primaryImage');

        $this->assertStringContainsString(
            (string) config('images.fallbacks.product'),
            $product->imageUrl('thumb'),
        );
    }
}
