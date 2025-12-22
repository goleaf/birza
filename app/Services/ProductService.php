<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductService
{
    private const IMAGE_PATH = 'public/products/';

    public function createProduct(array $validatedData): Product
    {
        return DB::transaction(function () use ($validatedData) {
            $product = Product::create($this->prepareProductData($validatedData));

            if (isset($validatedData['attributes'])) {
                $this->syncProductAttributes($product, $validatedData['attributes']);
            }

            $this->handleProductImages($product, $validatedData);

            return $product;
        });
    }

    public function updateProduct(Product $product, array $validatedData): void
    {
        DB::transaction(function () use ($product, $validatedData) {
            $product->update($this->prepareProductData($validatedData));

            if (isset($validatedData['attributes'])) {
                $this->syncProductAttributes($product, $validatedData['attributes']);
            }

            $this->handleProductImages($product, $validatedData);
        });
    }

    private function prepareProductData(array $data): array
    {
        return array_merge($data, [
            'is_active' => $data['is_active'] ?? false,
            'is_organic' => $data['is_organic'] ?? false,
        ]);
    }

    private function handleProductImages(Product $product, array $data): void
    {
        if (isset($data['product_image'])) {
            $product->product_image = $this->processImage(
                $data['product_image'],
                $product->product_image
            );
        }

        if (isset($data['product_additional_image'])) {
            $product->product_additional_image = $this->processImage(
                $data['product_additional_image'],
                $product->product_additional_image
            );
        }

        if ($product->isDirty(['product_image', 'product_additional_image'])) {
            $product->save();
        }
    }

    private function processImage($imageFile, ?string $oldImage = null): string
    {
        if ($oldImage) {
            Storage::delete(self::IMAGE_PATH . $oldImage);
        }

        $image = Image::make($imageFile)
            ->resize(500, 500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80);

        $filename = uniqid() . '.webp';
        Storage::put(self::IMAGE_PATH . $filename, $image);

        return $filename;
    }

    private function syncProductAttributes(Product $product, array $attributes): void
    {
        $formattedAttributes = collect($attributes)->map(function ($value, $attributeId) {
            return ['value' => $value];
        });

        $product->attributes()->sync($formattedAttributes);
    }

    public function softDeleteProduct(Product $product): void
    {
        $product->delete();
    }

    public function restoreProduct($id): void
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
    }

    public function forceDeleteProduct($id): void
    {
        $product = Product::withTrashed()->findOrFail($id);

        DB::transaction(function () use ($product) {
            $this->deleteProductImages($product);
            $product->attributes()->detach();
            $product->forceDelete();
        });
    }

    private function deleteProductImages(Product $product): void
    {
        if ($product->product_image) {
            Storage::delete(self::IMAGE_PATH . $product->product_image);
        }
        if ($product->product_additional_image) {
            Storage::delete(self::IMAGE_PATH . $product->product_additional_image);
        }
    }
}
