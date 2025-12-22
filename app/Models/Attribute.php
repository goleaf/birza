<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasJsonTranslations;

class Attribute extends Model
{
    use HasJsonTranslations, HasFactory;

    protected $table = 'attributes';

    public $timestamps = false;

    const TYPES = [
        'select' => 'Select',
        // 'text' => 'Text',
        'number' => 'Number',
        'boolean' => 'Boolean',
        'date' => 'Date'
    ];

    protected $fillable = [
        'name',
        'type',
        'is_filterable',
        'is_required',
        'is_active'
    ];

    public $translatable = ['name'];

    protected $casts = [
        'name' => 'json',
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attribute');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_value')
                    ->using(AttributeValue::class)
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    public function getActiveValuesForProduct($product)
    {
        if (!$product || !$product->exists) {
            return collect();
        }

        return $this->values()
            ->active()
            ->whereHas('products', function($query) use ($product) {
                $query->where('products.id', $product->getKey());
            })
            ->get();
    }
}
