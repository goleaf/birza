<?php

namespace App\Models;

use Database\Factories\ProductBundleItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundleItem extends Model
{
    /** @use HasFactory<ProductBundleItemFactory> */
    use HasFactory;

    protected $fillable = [
        'product_bundle_id',
        'product_id',
        'quantity',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_bundle_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function productBundle(): BelongsTo
    {
        return $this->belongsTo(ProductBundle::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->productBundle();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
