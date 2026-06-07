<?php

namespace App\Actions\Images;

use App\Support\Images\ImageVariantResult;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class GenerateImageVariantsAction
{
    /**
     * @param  array<string, mixed>  $typeConfig
     * @return array<string, array{contents: string, result: ImageVariantResult}>
     */
    public function handle(UploadedFile $file, array $typeConfig, string $directory, string $baseName): array
    {
        $variants = [];
        $format = (string) ($typeConfig['output_format'] ?? 'webp');
        $extension = $this->extensionForFormat($format);

        foreach ((array) ($typeConfig['variants'] ?? []) as $name => $variantConfig) {
            $image = Image::make($file->getRealPath())->orientate();
            $width = (int) $variantConfig['width'];
            $height = (int) $variantConfig['height'];

            if (($variantConfig['mode'] ?? 'contain') === 'cover') {
                $image->fit($width, $height, function ($constraint): void {
                    $constraint->upsize();
                });
            } else {
                $image->resize($width, $height, function ($constraint): void {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $contents = (string) $image->encode($format, (int) ($variantConfig['quality'] ?? 82));
            $path = trim($directory, '/').'/'.$name.'/'.$baseName.'.'.$extension;

            $variants[(string) $name] = [
                'contents' => $contents,
                'result' => new ImageVariantResult(
                    name: (string) $name,
                    path: $path,
                    width: $image->width(),
                    height: $image->height(),
                    size: strlen($contents),
                    mimeType: $this->mimeTypeForFormat($format),
                ),
            ];
        }

        return $variants;
    }

    private function extensionForFormat(string $format): string
    {
        return match ($format) {
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            default => 'webp',
        };
    }

    private function mimeTypeForFormat(string $format): string
    {
        return match ($format) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'image/webp',
        };
    }
}
