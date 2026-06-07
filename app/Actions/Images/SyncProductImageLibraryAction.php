<?php

namespace App\Actions\Images;

use App\Models\Product;
use App\Models\ProductImage;
use App\Support\Images\ImageUploadResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncProductImageLibraryAction
{
    public function __construct(
        private readonly UploadImageAction $uploadImageAction,
        private readonly DeleteImageAction $deleteImageAction,
    ) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $library
     * @param  array<int|string, mixed>  $files
     * @return Collection<int, ProductImage>
     */
    public function handle(Product $product, Collection $library, array $files = []): Collection
    {
        $product->loadMissing('images');

        $existingImages = $product->images
            ->keyBy(fn (ProductImage $image): string => $this->uuidFor($image));

        $prepared = [];
        $newPaths = [];

        try {
            foreach ($library->values() as $sortOrder => $media) {
                $uuid = (string) ($media['uuid'] ?? '');
                $file = $files[$sortOrder] ?? $files[(string) $sortOrder] ?? null;
                $existingImage = $existingImages->get($uuid);

                if ($file instanceof UploadedFile) {
                    $upload = $this->uploadImageAction->handle($file, 'product', [
                        'product_id' => $product->getKey(),
                    ]);
                    $newPaths = array_merge($newPaths, $upload->paths());

                    $prepared[] = [
                        'existing' => $existingImage,
                        'upload' => $upload,
                        'legacy_path' => null,
                        'sort_order' => (int) $sortOrder,
                    ];

                    continue;
                }

                if ($existingImage instanceof ProductImage) {
                    $prepared[] = [
                        'existing' => $existingImage,
                        'upload' => null,
                        'legacy_path' => null,
                        'sort_order' => (int) $sortOrder,
                    ];

                    continue;
                }

                $legacyPath = $this->legacyPathFromMedia($media);

                if ($legacyPath !== null) {
                    $prepared[] = [
                        'existing' => null,
                        'upload' => null,
                        'legacy_path' => $legacyPath,
                        'sort_order' => (int) $sortOrder,
                    ];
                }
            }

            $pathsToDelete = [];

            $syncedImages = DB::transaction(function () use ($product, $prepared, &$pathsToDelete): Collection {
                $keptIds = collect();

                foreach ($prepared as $index => $item) {
                    $image = $item['existing'] instanceof ProductImage
                        ? $item['existing']
                        : new ProductImage(['product_id' => $product->getKey()]);

                    $oldPaths = $image->exists ? $image->storedPaths() : [];

                    if ($item['upload'] instanceof ImageUploadResult) {
                        $this->fillFromUpload($image, $item['upload']);

                        if ($oldPaths !== []) {
                            $pathsToDelete[] = $oldPaths;
                        }
                    } elseif (is_string($item['legacy_path'])) {
                        $image->fill([
                            'type' => 'product',
                            'disk' => 'public',
                            'path' => $item['legacy_path'],
                            'original_path' => null,
                            'variants' => [],
                            'original_name' => basename($item['legacy_path']),
                            'mime_type' => null,
                            'size' => 0,
                            'width' => null,
                            'height' => null,
                        ]);
                    }

                    $image->fill([
                        'sort_order' => (int) $item['sort_order'],
                        'is_primary' => $index === 0,
                    ]);

                    if (! $image->alt_text) {
                        $image->alt_text = (string) $product->name;
                    }

                    $image->save();
                    $keptIds->push($image->getKey());
                }

                $staleImages = $product->images()
                    ->when($keptIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $keptIds->all()))
                    ->when($keptIds->isEmpty(), fn ($query) => $query)
                    ->get();

                foreach ($staleImages as $staleImage) {
                    $pathsToDelete[] = $staleImage->storedPaths();
                    $staleImage->delete();
                }

                $product->unsetRelation('images');
                $product->load('images');
                $product->syncLegacyImageColumnsFromImages();
                $product->save();

                return $product->images;
            });
        } catch (Throwable $throwable) {
            $this->deleteImageAction->handle($newPaths);

            throw $throwable;
        }

        collect($pathsToDelete)
            ->flatten()
            ->unique()
            ->values()
            ->whenNotEmpty(fn (Collection $paths) => $this->deleteImageAction->handle($paths->all()));

        return $syncedImages;
    }

    private function uuidFor(ProductImage $image): string
    {
        return 'product-image-'.$image->getKey();
    }

    /**
     * @param  array<string, mixed>  $media
     */
    private function legacyPathFromMedia(array $media): ?string
    {
        $path = $media['path'] ?? null;

        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = str($path)
            ->replaceStart('/storage/', '')
            ->replaceStart('storage/', '')
            ->replaceStart('public/', '')
            ->trim('/')
            ->toString();

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        return str_contains($path, '/') ? $path : 'products/'.$path;
    }

    private function fillFromUpload(ProductImage $image, ImageUploadResult $upload): void
    {
        $image->fill([
            'type' => 'product',
            'disk' => $upload->disk,
            'path' => $upload->path,
            'original_path' => $upload->originalPath,
            'variants' => $upload->variantMetadata(),
            'original_name' => $upload->originalName,
            'mime_type' => $upload->mimeType,
            'size' => $upload->size,
            'width' => $upload->width,
            'height' => $upload->height,
        ]);
    }
}
