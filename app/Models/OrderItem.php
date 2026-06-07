<?php

namespace App\Models;

use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'order_bundle_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'seller_id',
        'discount_id',
        'original_unit_price',
        'discount_amount',
        'final_unit_price',
        'product_title_snapshot',
        'product_price_snapshot',
        'seller_name_snapshot',
        'discount_source',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'original_unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'seller_id' => 'integer',
        'discount_id' => 'integer',
        'order_id' => 'integer',
        'order_bundle_id' => 'integer',
        'product_id' => 'integer',
        'product_price_snapshot' => 'decimal:2',
    ];

    /**
     * Get the order that owns this item
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for this order item, including soft deleted products
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function orderBundle(): BelongsTo
    {
        return $this->belongsTo(OrderBundle::class);
    }

    /**
     * Get the seller for this order item
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function scopeForSeller(Builder $query, Seller|int $seller): Builder
    {
        $sellerId = $seller instanceof Seller ? $seller->id : $seller;

        return $query->where('seller_id', $sellerId);
    }
}
