<?php

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'disk',
        'path',
        'original_path',
        'variants',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'caption',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'variants' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variantPath(string $variant = 'medium'): ?string
    {
        $variantPath = data_get($this->variants, $variant.'.path');

        if (is_string($variantPath) && $variantPath !== '') {
            return $variantPath;
        }

        if ($variant === 'original' && filled($this->original_path)) {
            return $this->original_path;
        }

        return $this->path;
    }

    public function url(string $variant = 'medium'): string
    {
        $path = $this->variantPath($variant);

        if (! is_string($path) || $path === '') {
            return $this->fallbackUrl();
        }

        if (! Storage::disk($this->disk)->exists($path)) {
            return $this->fallbackUrl();
        }

        return Storage::disk($this->disk)->url($path);
    }

    /**
     * @return array{uuid: string, url: string, path: ?string, alt: ?string, caption: ?string}
     */
    public function toLibraryItem(string $variant = 'medium'): array
    {
        return [
            'uuid' => 'product-image-'.$this->getKey(),
            'url' => $this->url($variant),
            'path' => $this->variantPath($variant),
            'alt' => $this->alt_text,
            'caption' => $this->caption,
        ];
    }

    /**
     * @return list<string>
     */
    public function storedPaths(): array
    {
        return collect([$this->path, $this->original_path])
            ->merge(collect($this->variants ?? [])->pluck('path'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function fallbackUrl(): string
    {
        return asset((string) config('images.fallbacks.product', 'images/admin-product-placeholder.svg'));
    }
}
