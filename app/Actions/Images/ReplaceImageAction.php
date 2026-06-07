<?php

namespace App\Actions\Images;

use App\Support\Images\ImageUploadResult;
use Illuminate\Http\UploadedFile;

class ReplaceImageAction
{
    public function __construct(
        private readonly UploadImageAction $uploadImageAction,
        private readonly DeleteImageAction $deleteImageAction,
    ) {}

    /**
     * @param  array<string, string|int|null>  $directoryReplacements
     * @param  array<string, mixed>|array<int, string>|string|null  $oldImage
     */
    public function handle(
        UploadedFile $file,
        string $type,
        array $directoryReplacements = [],
        array|string|null $oldImage = null,
        ?string $oldDisk = null,
    ): ImageUploadResult {
        $result = $this->uploadImageAction->handle($file, $type, $directoryReplacements);

        $this->deleteImageAction->handle($oldImage, $oldDisk);

        return $result;
    }
}
