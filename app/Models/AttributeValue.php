<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasJsonTranslations;

class AttributeValue extends Model
{
    use HasJsonTranslations, HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'is_active',
    ];

    public $translatable = ['value'];

    public $timestamps = false;

    protected $casts = [
        'value' => 'json',
        'is_active' => 'boolean',
        'attribute_id' => 'integer'
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_value')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAttribute($query, $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    public function scopeForProduct($query, $productId)
    {
        if (!$productId) {
            return $query;
        }

        return $query->whereHas('products', function($q) use ($productId) {
            $q->where('products.id', $productId);
        });
    }
}
