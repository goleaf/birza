<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
