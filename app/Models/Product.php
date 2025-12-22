<?php

namespace App\Models;

use App\Models\OrderItem;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations, SoftDeletes;

    public const UNITS = ['piece', 'kg', 'l', 'pack'];

    protected $table = 'products';
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
        'description',
        'is_active',
        'package_weight',
        'price_per_liter',
        'stock',
        'temperature_conditions_from',
        'temperature_conditions_to',
        'use_until',
        'total_shelf_life'
    ];

    public $translatable = ['description'];

    protected $casts = [
        'seller_id' => 'integer',
        'country_of_origin' => 'integer',
        'category_id' => 'integer',
        'is_organic' => 'boolean',
        'is_active' => 'boolean',
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
        'total_shelf_life' => 'integer'
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

    public function syncAttributeValues($attributes)
    {
        $attributeValueIds = collect($attributes)
            ->filter()
            ->values()
            ->all();

        $this->attributeValues()->sync($attributeValueIds);
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
            ->with(['values' => function($query) {
                $query->active();
            }])
            ->active()
            ->get() ?? collect();
    }

    public function getFormattedPackageWeightAttribute(): ?string
    {
        return $this->package_weight ? number_format($this->package_weight, 3) . ' kg' : null;
    }

    public function getFormattedPricePerLiterAttribute(): ?string
    {
        return $this->price_per_liter ? number_format($this->price_per_liter, 2) . ' €/L' : null;
    }

    public function getKey(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
