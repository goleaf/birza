<?php

namespace App\Actions\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ValidateImageUploadAction
{
    /**
     * @return array<int, mixed>
     */
    public function rules(string $type, bool $required = false): array
    {
        $config = $this->config($type);

        return [
            $required ? 'required' : 'nullable',
            File::image()
                ->max((int) $config['max_kb'])
                ->dimensions(
                    Rule::dimensions()
                        ->maxWidth((int) $config['max_width'])
                        ->maxHeight((int) $config['max_height'])
                ),
            'mimetypes:'.implode(',', (array) $config['allowed_mime_types']),
        ];
    }

    public function handle(UploadedFile $file, string $type, bool $required = true): void
    {
        Validator::make(
            ['file' => $file],
            ['file' => $this->rules($type, $required)]
        )->validate();

        $config = $this->config($type);

        if (($config['reject_animated'] ?? true) && $this->isAnimated($file)) {
            throw ValidationException::withMessages([
                'file' => __('validation.mimes', [
                    'attribute' => 'file',
                    'values' => implode(', ', (array) $config['allowed_extensions']),
                ]),
            ]);
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

    private function isAnimated(UploadedFile $file): bool
    {
        $path = $file->getRealPath();

        if (! is_string($path) || ! is_file($path)) {
            return false;
        }

        $contents = (string) file_get_contents($path, false, null, 0, 2_000_000);
        $mimeType = (string) $file->getMimeType();

        if ($mimeType === 'image/gif') {
            return preg_match_all('/\x00\x21\xF9\x04/', $contents) > 1;
        }

        if ($mimeType === 'image/webp') {
            return str_contains($contents, 'ANMF');
        }

        return false;
    }
}
