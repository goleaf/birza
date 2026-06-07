<?php

namespace App\Models;

use App\Http\Filters\ProductFilter;
use App\Models\Concerns\HasJsonTranslations;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Kettasoft\Filterable\Traits\HasFilterable;

class Product extends Model
{
    use HasFactory, HasFilterable, HasJsonTranslations, SoftDeletes;

    public const UNITS = ['piece', 'kg', 'l', 'pack'];

    protected $table = 'products';

    protected string $filterable = ProductFilter::class;

    protected $fillable = [
        'name',
        'category_id',
        'seller_id',
        'price',
        'pack_type',
        'min_order_price',
        'min_order_count',
        'unit',
        'is_organic',
        'country_of_origin',
        'product_image',
        'product_additional_image',
        'image_library',
        'description',
        'is_active',
        'package_weight',
        'price_per_liter',
        'stock',
        'temperature_conditions_from',
        'temperature_conditions_to',
        'use_until',
        'total_shelf_life',
    ];

    public $translatable = ['description'];

    protected $casts = [
        'seller_id' => 'integer',
        'country_of_origin' => 'integer',
        'category_id' => 'integer',
        'is_organic' => 'boolean',
        'is_active' => 'boolean',
        'image_library' => AsCollection::class,
        'price' => 'decimal:2',
        'min_order_price' => 'decimal:2',
        'min_order_count' => 'integer',
        'description' => 'json',
        'package_weight' => 'decimal:3',
        'price_per_liter' => 'decimal:2',
        'stock' => 'integer',
        'temperature_conditions_from' => 'integer',
        'temperature_conditions_to' => 'integer',
        'use_until' => 'date',
        'total_shelf_life' => 'integer',
    ];

    protected static function booted(): void
    {
        /*
        static::created(function ($product) {
            $product->name = md5($product->id);
            $product->save();
        });
        */
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value')
            ->withPivot('attribute_id')
            ->withTimestamps();
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute');
    }

    public function syncAttributeValues($attributes): void
    {
        $attributeValueIds = collect($attributes)
            ->filter()
            ->map(fn (mixed $attributeValueId): int => (int) $attributeValueId)
            ->values()
            ->all();

        $syncValues = AttributeValue::query()
            ->whereKey($attributeValueIds)
            ->pluck('attribute_id', 'id')
            ->mapWithKeys(
                fn (int $attributeId, int $attributeValueId): array => [
                    $attributeValueId => ['attribute_id' => $attributeId],
                ],
            )
            ->all();

        $this->attributeValues()->sync($syncValues);
    }

    /**
     * @return list<string>
     */
    public static function unitValues(): array
    {
        return collect(self::UNITS)
            ->sort()
            ->values()
            ->all();
    }

    public static function defaultUnit(): string
    {
        return self::unitValues()[0] ?? 'kg';
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public static function unitOptions(): array
    {
        return collect(self::unitValues())
            ->map(fn (string $unit): array => [
                'id' => $unit,
                'name' => __('units_unit_'.strtolower($unit)),
            ])
            ->values()
            ->all();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_of_origin');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true)
            ->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function publicAnsweredQuestions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class)->publicAnswered();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFallbackLocale(): string
    {
        return config('app.fallback_locale');
    }

    public function getCategoryAttributes()
    {
        return $this->category?->attributes()
            ->with(['values' => function ($query) {
                $query->active();
            }])
            ->active()
            ->get() ?? collect();
    }

    public function getFormattedPackageWeightAttribute(): ?string
    {
        return $this->package_weight ? number_format($this->package_weight, 3).' kg' : null;
    }

    public function getFormattedPricePerLiterAttribute(): ?string
    {
        return $this->price_per_liter ? number_format($this->price_per_liter, 2).' €/L' : null;
    }

    public function imageLibraryPreview(): Collection
    {
        $images = $this->productImageRecords();

        if ($images->isNotEmpty()) {
            return $images
                ->map(fn (ProductImage $image): array => $image->toLibraryItem('medium'))
                ->values();
        }

        if ($this->image_library instanceof Collection && $this->image_library->isNotEmpty()) {
            return $this->image_library
                ->map(fn (array $image): array => $this->normalizeLibraryImage($image))
                ->values();
        }

        return collect([$this->product_image, $this->product_additional_image])
            ->filter(fn (?string $fileName) => filled($fileName))
            ->map(fn (string $fileName) => [
                'uuid' => $fileName,
                'url' => $this->storageUrlForPath($this->normalizeImagePath($fileName)),
                'path' => $this->normalizeImagePath($fileName),
            ])
            ->values();
    }

    public function imageGalleryUrls(string $variant = 'large'): array
    {
        $images = $this->productImageRecords();

        if ($images->isNotEmpty()) {
            return $images
                ->map(fn (ProductImage $image): string => $image->url($variant))
                ->values()
                ->all();
        }

        return $this->imageLibraryPreview()
            ->pluck('url')
            ->filter(fn (?string $url) => filled($url))
            ->values()
            ->all();
    }

    public function imageUrl(string $variant = 'medium'): string
    {
        $image = $this->primaryImageRecord();

        if ($image instanceof ProductImage) {
            return $image->url($variant);
        }

        $legacyPath = $this->normalizeImagePath($this->product_image);

        if (filled($legacyPath)) {
            return $this->storageUrlForPath($legacyPath);
        }

        return $this->fallbackImageUrl();
    }

    public function fallbackImageUrl(): string
    {
        return asset((string) config('images.fallbacks.product', 'images/admin-product-placeholder.svg'));
    }

    public function syncLegacyImageColumnsFromLibrary(): void
    {
        $imageFiles = collect($this->image_library ?? [])
            ->pluck('path')
            ->map(fn (?string $path) => $path ? $this->normalizeImagePath($path) : null)
            ->filter()
            ->values();

        $this->product_image = $imageFiles->get(0, '');
        $this->product_additional_image = $imageFiles->get(1);
    }

    public function syncLegacyImageColumnsFromImages(): void
    {
        $imageFiles = $this->productImageRecords()
            ->map(fn (ProductImage $image): ?string => $image->variantPath('medium'))
            ->filter()
            ->values();

        $this->product_image = $imageFiles->get(0, '');
        $this->product_additional_image = $imageFiles->get(1);
        $this->image_library = $this->productImageRecords()
            ->map(fn (ProductImage $image): array => $image->toLibraryItem('medium'))
            ->values();
    }

    public function deleteStoredImages(): void
    {
        $paths = collect($this->image_library ?? [])
            ->pluck('path')
            ->merge(
                collect([$this->product_image, $this->product_additional_image])
                    ->filter()
                    ->map(fn (string $fileName) => $this->normalizeImagePath($fileName))
            )
            ->merge($this->productImageRecords()->flatMap->storedPaths())
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    public function getKey(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }


    public function stockAlerts(): HasMany
    {
        return $this->hasMany(ProductStockAlert::class);
    }

    public function isVisibleToBuyers(): bool
    {
        if ($this->trashed() || ! $this->is_active) {
            return false;
        }

        $seller = $this->relationLoaded('seller')
            ? $this->seller
            : $this->seller()->select(['id', 'is_active', 'deleted_at'])->first();

        return $seller !== null
            && ! $seller->trashed()
            && (bool) $seller->is_active;
    }

    public function isPurchasableByBuyers(): bool
    {
        return $this->isVisibleToBuyers() && (int) $this->stock > 0;
    }

    public function canReceiveStockAlerts(): bool
    {
        return $this->isVisibleToBuyers() && (int) $this->stock <= 0;
    }

    private function primaryImageRecord(): ?ProductImage
    {
        if ($this->relationLoaded('primaryImage')) {
            $primaryImage = $this->getRelation('primaryImage');

            return $primaryImage instanceof ProductImage ? $primaryImage : null;
        }

        $images = $this->productImageRecords();

        return $images->firstWhere('is_primary', true) ?? $images->first();
    }

    /**
     * @return Collection<int, ProductImage>
     */
    private function productImageRecords(): Collection
    {
        if ($this->relationLoaded('images')) {
            return $this->images->sortBy('sort_order')->values();
        }

        if (! $this->exists || ! Schema::hasTable('product_images')) {
            return collect();
        }

        return $this->images()->get();
    }

    /**
     * @param  array<string, mixed>  $image
     * @return array{uuid: string, url: string, path: ?string}
     */
    private function normalizeLibraryImage(array $image): array
    {
        $path = $this->normalizeImagePath($image['path'] ?? null);

        return [
            'uuid' => (string) ($image['uuid'] ?? $path ?? ''),
            'url' => $path ? $this->storageUrlForPath($path) : $this->fallbackImageUrl(),
            'path' => $path,
        ];
    }

    private function normalizeImagePath(?string $path): ?string
    {
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
            return $path;
        }

        return str_contains($path, '/') ? $path : 'products/'.$path;
    }

    private function storageUrlForPath(?string $path): string
    {
        if (! is_string($path) || $path === '') {
            return $this->fallbackImageUrl();
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (! Storage::disk('public')->exists($path)) {
            return $this->fallbackImageUrl();
        }

        return Storage::disk('public')->url($path);
    }
}
