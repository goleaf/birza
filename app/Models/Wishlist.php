<?php

namespace App\Models;

use App\Models\Users\Buyer;
use Database\Factories\WishlistFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wishlist extends Model
{
    /** @use HasFactory<WishlistFactory> */
    use HasFactory;

    public const DEFAULT_NAME_KEY = 'wishlists.default_name';

    protected $fillable = [
        'buyer_id',
        'name',
        'slug',
        'description',
        'is_default',
        'is_private',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'buyer_id' => 'integer',
            'is_default' => 'boolean',
            'is_private' => 'boolean',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlist_items')
            ->withTimestamps();
    }

    public function scopeForBuyer(Builder $query, Buyer $buyer): Builder
    {
        return $query
            ->select(['id', 'buyer_id', 'name', 'slug', 'description', 'is_default', 'is_private', 'created_at', 'updated_at'])
            ->where('buyer_id', $buyer->id);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query
            ->select(['id', 'buyer_id', 'name', 'slug', 'description', 'is_default', 'is_private', 'created_at', 'updated_at'])
            ->where('is_default', true);
    }

    public function isOwnedBy(Buyer $buyer): bool
    {
        return (int) $this->buyer_id === (int) $buyer->id;
    }
}
