<?php

namespace App\Actions\Images;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class DeleteImageAction
{
    /**
     * @param  ProductImage|array<string, mixed>|array<int, string>|string|null  $image
     */
    public function handle(ProductImage|array|string|null $image, ?string $disk = null): void
    {
        $paths = $this->paths($image);
        $diskName = $disk ?? ($image instanceof ProductImage ? $image->disk : 'public');

        if ($paths !== []) {
            Storage::disk($diskName)->delete($paths);
        }
    }

    /**
     * @param  ProductImage|array<string, mixed>|array<int, string>|string|null  $image
     * @return list<string>
     */
    public function paths(ProductImage|array|string|null $image): array
    {
        if ($image === null) {
            return [];
        }

        if (is_string($image)) {
            return [$image];
        }

        if ($image instanceof ProductImage) {
            return $image->storedPaths();
        }

        if (array_is_list($image)) {
            return collect($image)->filter()->unique()->values()->all();
        }

        return collect([
            $image['path'] ?? null,
            $image['original_path'] ?? null,
        ])
            ->merge(collect($image['variants'] ?? [])->pluck('path'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
