<?php

namespace Tests\Feature\Images;

use App\Actions\Images\DeleteImageAction;
use App\Actions\Images\ReplaceImageAction;
use App\Actions\Images\SyncProductImageLibraryAction;
use App\Actions\Images\UploadImageAction;
use App\Actions\Images\ValidateImageUploadAction;
use App\Enums\OrderPaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductImagePipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_product_image_upload_generates_relative_webp_variants(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();

        $result = app(UploadImageAction::class)->handle(
            UploadedFile::fake()->image('dangerous-name.php.jpg', 1800, 1200),
            'product',
            ['product_id' => $product->id],
        );

        $this->assertRelativeStoragePath($result->path);
        $this->assertStringStartsWith("images/products/{$product->id}/medium/", $result->path);
        $this->assertStringEndsWith('.webp', $result->path);
        $this->assertSame(['thumb', 'small', 'medium', 'large'], array_keys($result->variants));

        $generatedVariantPaths = collect($result->variants)
            ->map(fn ($variant): string => $variant->path)
            ->values();

        foreach ($generatedVariantPaths as $path) {
            $this->assertRelativeStoragePath($path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_invalid_product_image_files_are_rejected(): void
    {
        Storage::fake('public');

        $validator = app(ValidateImageUploadAction::class);

        try {
            $validator->handle(
                UploadedFile::fake()->create('not-image.pdf', 100, 'application/pdf'),
                'product',
            );

            $this->fail('The PDF upload was not rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->expectException(ValidationException::class);

        $validator->handle(
            UploadedFile::fake()->image('too-large.jpg')->size(((int) config('images.types.product.max_kb')) + 1),
            'product',
        );
    }

    public function test_replacing_an_image_deletes_old_paths_only_after_successful_upload(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $oldPaths = [
            'images/products/'.$product->id.'/thumb/old.webp',
            'images/products/'.$product->id.'/medium/old.webp',
            'images/products/'.$product->id.'/large/old.webp',
        ];

        foreach ($oldPaths as $oldPath) {
            Storage::disk('public')->put($oldPath, 'old-image');
        }

        try {
            app(ReplaceImageAction::class)->handle(
                UploadedFile::fake()->create('invalid.pdf', 100, 'application/pdf'),
                'product',
                ['product_id' => $product->id],
                $oldPaths,
                'public',
            );

            $this->fail('The invalid replacement upload was not rejected.');
        } catch (ValidationException) {
            Storage::disk('public')->assertExists($oldPaths);
        }

        $replacement = app(ReplaceImageAction::class)->handle(
            UploadedFile::fake()->image('replacement.png', 1600, 1000),
            'product',
            ['product_id' => $product->id],
            $oldPaths,
            'public',
        );

        Storage::disk('public')->assertMissing($oldPaths);
        Storage::disk('public')->assertExists($replacement->paths());
    }

    public function test_product_gallery_images_can_be_added_reordered_and_deleted(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();

        app(SyncProductImageLibraryAction::class)->handle(
            $product,
            collect([
                ['uuid' => 'new-primary', 'url' => 'temporary-primary'],
                ['uuid' => 'new-secondary', 'url' => 'temporary-secondary'],
            ]),
            [
                0 => UploadedFile::fake()->image('primary.jpg', 1600, 1200),
                1 => UploadedFile::fake()->image('secondary.jpg', 1600, 1200),
            ],
        );

        $product->refresh()->load('images');

        $this->assertCount(2, $product->images);
        $this->assertTrue($product->images->first()->is_primary);
        $this->assertSame(0, $product->images->first()->sort_order);
        $this->assertSame($product->images->first()->variantPath('medium'), $product->product_image);
        $this->assertSame($product->images->get(1)->variantPath('medium'), $product->product_additional_image);
        $this->assertDatabaseCount('product_images', 2);

        $deletedImage = $product->images->first();
        $keptImage = $product->images->get(1);
        $deletedPaths = $deletedImage->storedPaths();

        app(SyncProductImageLibraryAction::class)->handle(
            $product,
            collect([$keptImage->toLibraryItem('medium')]),
        );

        $product->refresh()->load('images');

        $this->assertCount(1, $product->images);
        $this->assertTrue($product->images->first()->is_primary);
        $this->assertSame(0, $product->images->first()->sort_order);
        $this->assertSame($keptImage->id, $product->images->first()->id);
        Storage::disk('public')->assertMissing($deletedPaths);
        $remainingImage = $product->images->first();
        $remainingDisplayPaths = collect([$remainingImage->path])
            ->merge(collect($remainingImage->variants ?? [])->pluck('path'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        Storage::disk('public')->assertExists($remainingDisplayPaths);
    }

    public function test_delete_image_action_removes_all_generated_variants(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $image = ProductImage::factory()->for($product)->create([
            'path' => 'images/products/'.$product->id.'/medium/main.webp',
            'original_path' => 'images/products/'.$product->id.'/original/main.jpg',
            'variants' => [
                'thumb' => ['path' => 'images/products/'.$product->id.'/thumb/main.webp'],
                'small' => ['path' => 'images/products/'.$product->id.'/small/main.webp'],
                'large' => ['path' => 'images/products/'.$product->id.'/large/main.webp'],
            ],
        ]);

        foreach ($image->storedPaths() as $path) {
            Storage::disk('public')->put($path, 'image');
        }

        app(DeleteImageAction::class)->handle($image);

        Storage::disk('public')->assertMissing($image->storedPaths());
    }

    public function test_missing_product_image_uses_configured_fallback(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'product_image' => '',
            'product_additional_image' => null,
            'image_library' => null,
        ]);

        $this->assertStringContainsString((string) config('images.fallbacks.product'), $product->imageUrl());

        ProductImage::factory()->for($product)->create([
            'is_primary' => true,
            'path' => 'images/products/'.$product->id.'/medium/missing.webp',
            'variants' => [
                'thumb' => ['path' => 'images/products/'.$product->id.'/thumb/missing.webp'],
            ],
        ]);

        $product->refresh()->load('primaryImage');

        $this->assertStringContainsString((string) config('images.fallbacks.product'), $product->imageUrl('thumb'));
    }

    public function test_seller_cannot_manage_another_sellers_product_images(): void
    {
        $owner = Seller::factory()->create();
        $otherSeller = Seller::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $owner->id,
        ]);

        $this->actingAs($otherSeller, 'seller')
            ->get(route('seller.products.edit', $product))
            ->assertForbidden();
    }

    public function test_order_history_still_renders_after_product_image_files_are_deleted(): void
    {
        Storage::fake('public');

        $buyer = Buyer::factory()->create();
        $seller = Seller::factory()->create();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'name' => 'History-safe product',
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'payment_status' => OrderPaymentStatus::Pending,
        ]);
        $image = ProductImage::factory()->for($product)->create([
            'is_primary' => true,
            'path' => 'images/products/'.$product->id.'/medium/history.webp',
            'variants' => [
                'thumb' => ['path' => 'images/products/'.$product->id.'/thumb/history.webp'],
            ],
        ]);

        foreach ($image->storedPaths() as $path) {
            Storage::disk('public')->put($path, 'image');
        }

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'quantity' => 2,
        ]);

        app(DeleteImageAction::class)->handle($image);

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.orders.show', $order))
            ->assertOk()
            ->assertSee('History-safe product')
            ->assertSee((string) config('images.fallbacks.product'));
    }

    private function assertRelativeStoragePath(string $path): void
    {
        $this->assertStringNotContainsString(storage_path(), $path);
        $this->assertFalse(str_starts_with($path, '/'));
        $this->assertFalse(str_starts_with($path, 'http://'));
        $this->assertFalse(str_starts_with($path, 'https://'));
    }
}
