<?php

namespace App\Actions\Images;

use App\Support\Images\ImageUploadResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use RuntimeException;
use Throwable;

class UploadImageAction
{
    public function __construct(
        private readonly ValidateImageUploadAction $validateImageUploadAction,
        private readonly GenerateImageVariantsAction $generateImageVariantsAction,
        private readonly DeleteImageAction $deleteImageAction,
    ) {}

    /**
     * @param  array<string, string|int|null>  $directoryReplacements
     */
    public function handle(UploadedFile $file, string $type, array $directoryReplacements = []): ImageUploadResult
    {
        $this->validateImageUploadAction->handle($file, $type);

        $config = $this->config($type);
        $disk = (string) ($config['disk'] ?? 'public');
        $directory = $this->directory($config, $directoryReplacements);
        $baseName = (string) Str::uuid();
        $original = Image::make($file->getRealPath())->orientate();
        $originalPath = null;
        $writtenPaths = [];

        try {
            if ($config['keep_original'] ?? false) {
                $originalPath = $directory.'/original/'.$baseName.'.'.$this->extensionForMimeType((string) $file->getMimeType());
                $this->put($disk, $originalPath, (string) file_get_contents($file->getRealPath()));
                $writtenPaths[] = $originalPath;
            }

            $generatedVariants = $this->generateImageVariantsAction->handle($file, $config, $directory, $baseName);
            $variants = [];

            foreach ($generatedVariants as $name => $variant) {
                $result = $variant['result'];
                $this->put($disk, $result->path, $variant['contents']);
                $writtenPaths[] = $result->path;
                $variants[$name] = $result;
            }

            $path = $variants['medium']->path
                ?? $variants['large']->path
                ?? $variants['small']->path
                ?? $variants['thumb']->path
                ?? $originalPath;

            if (! is_string($path) || $path === '') {
                throw new RuntimeException('No image path was generated.');
            }

            return new ImageUploadResult(
                disk: $disk,
                path: $path,
                originalPath: $originalPath,
                originalName: $file->getClientOriginalName(),
                mimeType: (string) $file->getMimeType(),
                size: (int) $file->getSize(),
                width: $original->width(),
                height: $original->height(),
                variants: $variants,
            );
        } catch (Throwable $throwable) {
            $this->deleteImageAction->handle($writtenPaths, $disk);

            throw $throwable;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function config(string $type): array
    {
        $config = config("images.types.$type");

        if (! is_array($config)) {
            throw new RuntimeException("Image type [$type] is not configured.");
        }

        return $config;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, string|int|null>  $replacements
     */
    private function directory(array $config, array $replacements): string
    {
        $directory = (string) $config['directory'];

        foreach ($replacements as $key => $value) {
            $directory = str_replace('{'.$key.'}', (string) $value, $directory);
        }

        return trim($directory, '/');
    }

    private function extensionForMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };
    }

    private function put(string $disk, string $path, string $contents): void
    {
        if (! Storage::disk($disk)->put($path, $contents, 'public')) {
            throw new RuntimeException("Unable to write image [$path] to disk [$disk].");
        }
    }
}
