<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
