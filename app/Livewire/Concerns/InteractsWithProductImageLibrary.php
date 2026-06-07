<?php

namespace App\Livewire\Concerns;

use App\Actions\Images\SyncProductImageLibraryAction;
use App\Actions\Images\ValidateImageUploadAction;
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
     * @return array<string, array<int, mixed>>
     */
    protected function productImageLibraryRules(): array
    {
        return [
            'imageFiles.*' => app(ValidateImageUploadAction::class)->rules('product'),
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

    protected function syncProductImageLibrary(Product $product): void
    {
        if (($product->image_library === null || $product->image_library->isEmpty())
            && ($product->product_image || $product->product_additional_image)) {
            $product->image_library = $product->imageLibraryPreview();
        }

        app(SyncProductImageLibraryAction::class)->handle($product, $this->imageLibrary, $this->imageFiles);

        $this->imageFiles = [];
        $this->imageLibrary = $product->fresh()?->imageLibraryPreview() ?? collect();
    }
}
