<?php

namespace App\Livewire\Concerns;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mary\Traits\WithMediaSync;

trait InteractsWithProductImageLibrary
{
    use WithMediaSync;

    public array $imageFiles = [];

    public Collection $imageLibrary;

    protected function initializeProductImageLibrary(?Product $product = null): void
    {
        $this->imageFiles = [];
        $this->imageLibrary = $product?->imageLibraryPreview() ?? collect();
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function productImageLibraryRules(): array
    {
        return [
            'imageFiles.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:15048'],
        ];
    }

    protected function ensureProductImageLibraryIsPresent(): void
    {
        if ($this->imageLibrary->isEmpty()) {
            throw ValidationException::withMessages([
                'imageLibrary' => __('validation_required', ['attribute' => __('common_product_images')]),
            ]);
        }
    }

    protected function pendingProductImageFileNames(): Collection
    {
        return $this->imageLibrary
            ->map(function (array $media): ?string {
                $path = $media['path'] ?? null;

                if (is_string($path) && $path !== '') {
                    return basename($path);
                }

                $uuid = $media['uuid'] ?? null;
                $url = $media['url'] ?? null;

                if (! is_string($uuid) || ! is_string($url)) {
                    return null;
                }

                $extension = str($url)->before('?')->afterLast('.')->toString();

                return $extension !== '' ? $uuid.'.'.$extension : null;
            })
            ->filter()
            ->values();
    }

    protected function syncProductImageLibrary(Product $product): void
    {
        if (($product->image_library === null || $product->image_library->isEmpty())
            && ($product->product_image || $product->product_additional_image)) {
            $product->image_library = $product->imageLibraryPreview();
        }

        $this->syncMedia(
            model: $product,
            library: 'imageLibrary',
            files: 'imageFiles',
            storage_subpath: 'products',
            model_field: 'image_library',
        );

        $product->syncLegacyImageColumnsFromLibrary();
        $product->save();
    }
}
