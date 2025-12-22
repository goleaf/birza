<?php

namespace App\Models;

use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Models\Product;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use SoftDeletes, HasFactory;

    public const STATUS = [
        'PENDING' => 'pending',
        'PAID' => 'paid',
        'FAILED' => 'failed',
        'PROCESSING' => 'processing',
        'SHIPPED' => 'shipped',
        'DELIVERED' => 'delivered',
        'CANCELLED' => 'cancelled',
        'REFUNDED' => 'refunded'
    ];

    protected $fillable = [
        'order_total',
        'buyer_id', 
        'payment_method',
        'payment_status',
        'status'
    ];

    protected $casts = [
        'order_total' => 'decimal:2',
        'buyer_id' => 'integer'
    ];

    /**
     * Get the buyer associated with the order
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class)->withTrashed();
    }

    /**
     * Get the sellers associated with the order through order items
     */
    public function sellers()
    {
        return $this->hasManyThrough(
            Seller::class,
            OrderItem::class,
            'order_id', // Foreign key on order_items table
            'id', // Foreign key on sellers table
            'id', // Local key on orders table
            'seller_id' // Local key on order_items table
        )->withTrashed();
    }

    /**
     * Get the product associated with the order
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the country associated with the order
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_of_origin');
    }

    /**
     * Get the order items for the order
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the total price of the order
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->order_total;
    }

    /**
     * Scope a query to only include pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS['PENDING']);
    }

    /**
     * Scope a query to only include paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS['PAID']);
    }

    /**
     * Scope a query to only include failed orders
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS['FAILED']);
    }

    /**
     * Scope a query to include orders with full details
     */
    public function scopeWithFullDetails($query)
    {
        return $query->with(['buyer', 'orderItems.product', 'orderItems.seller']);
    }

    /**
     * Get the order items for the order
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the products associated with the order
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'total_price', 'seller_id'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }
}
