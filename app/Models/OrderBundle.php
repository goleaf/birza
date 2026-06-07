<?php

namespace App\Models;

use App\Models\Users\Seller;
use Database\Factories\OrderBundleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderBundle extends Model
{
    /** @use HasFactory<OrderBundleFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_bundle_id',
        'seller_id',
        'bundle_name_snapshot',
        'quantity',
        'base_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'final_price',
        'products_snapshot',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'product_bundle_id' => 'integer',
            'seller_id' => 'integer',
            'quantity' => 'integer',
            'base_price' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'products_snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function productBundle(): BelongsTo
    {
        return $this->belongsTo(ProductBundle::class)->withTrashed();
    }

    public function bundle(): BelongsTo
    {
        return $this->productBundle();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
