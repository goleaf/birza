<?php

namespace App\Models;

use App\Models\Users\Buyer;
use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CONVERTED = 'converted';

    protected $fillable = [
        'user_id',
        'guest_token',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function bundleItems(): HasMany
    {
        return $this->hasMany(CartBundleItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->items();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
