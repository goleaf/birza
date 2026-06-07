<?php

namespace App\Models;

use App\Http\Filters\ProductFilter;
use App\Models\Concerns\HasJsonTranslations;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
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

    protected static function booted()
    {
        /*
        static::created(function ($product) {
            $product->name = md5($product->id);
            $product->save();
        });
        */
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value');
    }

    public function attributes()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value');
    }

    public function syncAttributeValues($attributes)
    {
        $attributeValueIds = collect($attributes)
            ->filter()
            ->values()
            ->all();

        $this->attributeValues()->sync($attributeValueIds);
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
        if ($this->image_library instanceof Collection && $this->image_library->isNotEmpty()) {
            return $this->image_library->values();
        }

        return collect([$this->product_image, $this->product_additional_image])
            ->filter(fn (?string $fileName) => filled($fileName))
            ->map(fn (string $fileName) => [
                'uuid' => $fileName,
                'url' => Storage::disk('public')->url('products/'.$fileName),
                'path' => 'products/'.$fileName,
            ])
            ->values();
    }

    public function imageGalleryUrls(): array
    {
        return $this->imageLibraryPreview()
            ->pluck('url')
            ->filter(fn (?string $url) => filled($url))
            ->values()
            ->all();
    }

    public function syncLegacyImageColumnsFromLibrary(): void
    {
        $imageFiles = collect($this->image_library ?? [])
            ->pluck('path')
            ->map(fn (?string $path) => $path ? basename($path) : null)
            ->filter()
            ->values();

        $this->product_image = $imageFiles->get(0);
        $this->product_additional_image = $imageFiles->get(1);
    }

    public function deleteStoredImages(): void
    {
        $paths = collect($this->image_library ?? [])
            ->pluck('path')
            ->merge(
                collect([$this->product_image, $this->product_additional_image])
                    ->filter()
                    ->map(fn (string $fileName) => 'products/'.$fileName)
            )
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
}
