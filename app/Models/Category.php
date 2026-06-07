<?php

namespace App\Models;

use App\Models\Concerns\HasJsonTranslations;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory, HasJsonTranslations;

    private const CACHE_TTL_SECONDS = 21_600;

    protected $table = 'categories';

    protected $fillable = [
        'parent_category_id',
        'category_name',
        'order',
        'slug',
    ];

    protected $casts = [
        'order' => 'integer',
        'parent_category_id' => 'integer',
        'category_name' => 'json',
        'slug' => 'json',
    ];

    public $translatable = ['category_name', 'slug'];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::saved(fn (): bool => self::flushReferenceCache());
        static::deleted(fn (): bool => self::flushReferenceCache());
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_category_id')->withDefault();
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attribute');
    }

    public function filterableAttributes()
    {
        return $this->attributes()
            ->where('is_filterable', true)
            ->where('is_active', true)
            ->with(['values' => function ($query) {
                $query->where('is_active', true);
            }]);
    }

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'seller_categories')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithRelationsForSeller($query)
    {
        $sellerId = auth()->guard('seller')->id();

        return $query->with('parent')
            ->withCount(['products' => function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            }])
            ->with(['subcategories' => function ($query) use ($sellerId) {
                $query->withCount(['products' => function ($query) use ($sellerId) {
                    $query->where('seller_id', $sellerId);
                }]);
            }]);
    }

    /**
     * @return Collection<int, self>
     */
    public static function cachedFilterTree(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        return Cache::remember(
            "categories.filters.locale.{$locale}",
            self::CACHE_TTL_SECONDS,
            fn (): Collection => self::query()
                ->select(['id', 'category_name', 'parent_category_id', 'order'])
                ->active()
                ->whereNull('parent_category_id')
                ->with(['subcategories' => function ($query): void {
                    $query->select(['id', 'category_name', 'parent_category_id', 'order'])
                        ->active()
                        ->with(['attributes' => function ($attributeQuery): void {
                            $attributeQuery
                                ->select([
                                    'attributes.id',
                                    'attributes.name',
                                    'attributes.is_required',
                                ])
                                ->where('is_active', true)
                                ->where('is_filterable', true)
                                ->with(['values' => function ($valueQuery): void {
                                    $valueQuery
                                        ->select(['id', 'attribute_id', 'value'])
                                        ->where('is_active', true)
                                        ->orderBy('value');
                                }]);
                        }])
                        ->orderBy('order')
                        ->orderBy('id');
                }])
                ->orderBy('order')
                ->orderBy('id')
                ->get(),
        );
    }

    /**
     * @return Collection<int, self>
     */
    public static function cachedVisibleTree(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        return Cache::remember(
            "categories.tree.locale.{$locale}",
            self::CACHE_TTL_SECONDS,
            fn (): Collection => self::query()
                ->select(['id', 'category_name', 'parent_category_id', 'order'])
                ->active()
                ->whereNull('parent_category_id')
                ->with(['subcategories' => function ($query): void {
                    $query->select(['id', 'category_name', 'parent_category_id', 'order'])
                        ->active()
                        ->orderBy('order')
                        ->orderBy('id');
                }])
                ->orderBy('order')
                ->orderBy('id')
                ->get(),
        );
    }

    public static function flushReferenceCache(): bool
    {
        foreach ((array) config('app.locales', []) as $locale) {
            Cache::forget("categories.filters.locale.{$locale}");
            Cache::forget("categories.tree.locale.{$locale}");
            Cache::forget("categories.visible.locale.{$locale}");
        }

        return true;
    }

    public function getAllProductsCountAttribute(): int
    {
        return $this->directProductsCount() + $this->subcategoryProductsCount();
    }

    private function directProductsCount(): int
    {
        if (array_key_exists('products_count', $this->attributes)) {
            return (int) $this->attributes['products_count'];
        }

        if ($this->relationLoaded('products')) {
            return $this->products->count();
        }

        return $this->products()->count();
    }

    private function subcategoryProductsCount(): int
    {
        if ($this->relationLoaded('subcategories')) {
            $subcategoryIds = $this->subcategories
                ->pluck($this->getKeyName())
                ->filter()
                ->all();

            if ($subcategoryIds === []) {
                return 0;
            }

            return Product::query()
                ->whereIn('category_id', $subcategoryIds)
                ->count();
        }

        return Product::query()
            ->whereIn('category_id', $this->subcategories()->select($this->getKeyName()))
            ->count();
    }
}
