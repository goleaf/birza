<?php

namespace App\Support\Images;

class ImageUploadResult
{
    /**
     * @param  array<string, ImageVariantResult>  $variants
     */
    public function __construct(
        public readonly string $disk,
        public readonly string $path,
        public readonly ?string $originalPath,
        public readonly string $originalName,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly int $width,
        public readonly int $height,
        public readonly array $variants,
    ) {}

    /**
     * @return array<string, array{path: string, width: int, height: int, size: int, mime_type: string}>
     */
    public function variantMetadata(): array
    {
        return collect($this->variants)
            ->map(fn (ImageVariantResult $variant): array => $variant->toArray())
            ->all();
    }

    /**
     * @return list<string>
     */
    public function paths(): array
    {
        return collect([$this->path, $this->originalPath])
            ->merge(collect($this->variants)->pluck('path'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
