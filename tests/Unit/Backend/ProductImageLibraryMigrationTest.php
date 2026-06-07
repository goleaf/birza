<?php

namespace Tests\Unit\Backend;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageLibraryMigrationTest extends TestCase
{
    public function test_image_library_preview_falls_back_to_legacy_columns(): void
    {
        Storage::fake('public');

        $product = (new Product)->forceFill([
            'product_image' => 'primary.webp',
            'product_additional_image' => 'secondary.webp',
        ]);

        $preview = $product->imageLibraryPreview();

        $this->assertCount(2, $preview);
        $this->assertSame('products/primary.webp', $preview->first()['path']);
        $this->assertSame('products/secondary.webp', $preview->get(1)['path']);
    }

    public function test_sync_legacy_image_columns_from_library_uses_first_two_images(): void
    {
        $product = new Product;
        $product->image_library = new Collection([
            ['uuid' => 'one', 'path' => 'products/first.webp', 'url' => '/storage/products/first.webp'],
            ['uuid' => 'two', 'path' => 'products/second.webp', 'url' => '/storage/products/second.webp'],
            ['uuid' => 'three', 'path' => 'products/third.webp', 'url' => '/storage/products/third.webp'],
        ]);

        $product->syncLegacyImageColumnsFromLibrary();

        $this->assertSame('products/first.webp', $product->product_image);
        $this->assertSame('products/second.webp', $product->product_additional_image);
    }
}
