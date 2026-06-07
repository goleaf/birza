<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $table = 'product_attribute_value';

    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_value_id',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'attribute_id' => 'integer',
        'attribute_value_id' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
