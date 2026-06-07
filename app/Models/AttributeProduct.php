<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeProduct extends Model
{
    protected $table = 'attribute_product';

    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'product_id',
        'selected_value_id',
    ];

    protected $casts = [
        'attribute_id' => 'integer',
        'product_id' => 'integer',
        'selected_value_id' => 'integer',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function selectedValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class, 'selected_value_id');
    }
}
