<?php

namespace App\Models;

use Database\Factories\CartBundleItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartBundleItem extends Model
{
    /** @use HasFactory<CartBundleItemFactory> */
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_bundle_id',
        'quantity',
        'unit_price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cart_id' => 'integer',
            'product_bundle_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function productBundle(): BelongsTo
    {
        return $this->belongsTo(ProductBundle::class)->withTrashed();
    }

    public function bundle(): BelongsTo
    {
        return $this->productBundle();
    }
}
