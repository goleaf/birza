<?php

namespace Database\Seeders\Demo;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoProductImageSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('product_images')) {
            return;
        }

        Product::query()
            ->where('name', 'like', 'Demo %')
            ->where('product_image', '!=', '')
            ->orderBy('id')
            ->each(function (Product $product): void {
                $imageCount = $product->name === 'Demo Published Product' ? 3 : 1;

                for ($index = 0; $index < $imageCount; $index++) {
                    $this->seedImage($product, $index);
                }

                $product->syncLegacyImageColumnsFromImages();
                $product->save();
            });
    }

    private function seedImage(Product $product, int $sortOrder): ProductImage
    {
        $basePath = sprintf(
            'images/products/%d/%s-%02d',
            $product->getKey(),
            Str::slug($product->name ?? 'demo-product'),
            $sortOrder + 1,
        );

        $variants = [
            'thumb' => ['path' => $basePath.'/thumb.svg', 'width' => 160, 'height' => 120],
            'medium' => ['path' => $basePath.'/medium.svg', 'width' => 640, 'height' => 480],
            'large' => ['path' => $basePath.'/large.svg', 'width' => 1200, 'height' => 900],
        ];

        $image = ProductImage::query()->updateOrCreate([
            'product_id' => $product->getKey(),
            'sort_order' => $sortOrder,
        ], [
            'type' => 'product',
            'disk' => 'public',
            'path' => $variants['medium']['path'],
            'original_path' => $basePath.'/original.svg',
            'variants' => $variants,
            'original_name' => Str::slug($product->name ?? 'demo-product').'.svg',
            'mime_type' => 'image/svg+xml',
            'size' => 1024,
            'width' => 640,
            'height' => 480,
            'alt_text' => $product->name,
            'caption' => $sortOrder === 0 ? 'Primary demo image' : 'Gallery demo image',
            'is_primary' => $sortOrder === 0,
        ]);

        foreach ($image->storedPaths() as $path) {
            $this->writePlaceholder($path, $product, $sortOrder);
        }

        return $image;
    }

    private function writePlaceholder(string $path, Product $product, int $sortOrder): void
    {
        $label = e($product->name ?? 'Demo product');
        $color = substr(md5(($product->name ?? 'product').':'.$sortOrder), 0, 6);

        Storage::disk('public')->put($path, <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480" role="img" aria-label="{$label}">
  <rect width="640" height="480" fill="#{$color}"/>
  <rect x="32" y="32" width="576" height="416" fill="rgba(255,255,255,0.84)"/>
  <text x="320" y="236" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" fill="#1f2937">{$label}</text>
  <text x="320" y="278" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="#4b5563">Local demo image</text>
</svg>
SVG);
    }
}
