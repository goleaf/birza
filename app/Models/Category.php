<?php

namespace App\Models;

use App\Models\Concerns\HasJsonTranslations;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasJsonTranslations;

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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
