<?php

namespace App\Support\Images;

class ImageVariantResult
{
    public function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly int $width,
        public readonly int $height,
        public readonly int $size,
        public readonly string $mimeType,
    ) {}

    /**
     * @return array{path: string, width: int, height: int, size: int, mime_type: string}
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
        ];
    }
}
