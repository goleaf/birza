<?php

namespace App\Models;

use App\Models\Concerns\HasJsonTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributeValue extends Model
{
    use HasFactory, HasJsonTranslations;

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
        'attribute_id' => 'integer',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attribute_value')
            ->withPivot('attribute_id')
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
        if (! $productId) {
            return $query;
        }

        return $query->whereHas('products', function ($q) use ($productId) {
            $q->where('products.id', $productId);
        });
    }
}
