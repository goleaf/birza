<?php

namespace App\Models;

use App\Models\Users\Seller;
use Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Discount extends Model
{
    /** @use HasFactory<DiscountFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED_AMOUNT = 'fixed_amount';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'seller_id',
        'product_id',
        'category_id',
        'name',
        'type',
        'value',
        'starts_at',
        'ends_at',
        'status',
        'usage_limit',
        'used_count',
        'minimum_order_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seller_id' => 'integer',
            'product_id' => 'integer',
            'category_id' => 'integer',
            'value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'minimum_order_amount' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeForSeller(Builder $query, Seller|int $seller): Builder
    {
        $sellerId = $seller instanceof Seller ? $seller->id : $seller;

        return $query->where('seller_id', $sellerId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAvailableAt(Builder $query, ?Carbon $date = null): Builder
    {
        $date ??= now();

        return $query
            ->active()
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $date);
            })
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $date);
            })
            ->where(function (Builder $query): void {
                $query->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }

    public function isActiveAt(?Carbon $date = null): bool
    {
        $date ??= now();

        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isAfter($date)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isBefore($date)) {
            return false;
        }

        return $this->hasUsageRemaining();
    }

    public function hasUsageRemaining(): bool
    {
        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }

    public function canApplyTo(Product $product, float $sellerSubtotal): bool
    {
        if ((int) $this->seller_id !== (int) $product->seller_id) {
            return false;
        }

        if ($this->product_id !== null && (int) $this->product_id !== (int) $product->id) {
            return false;
        }

        if ($this->category_id !== null && (int) $this->category_id !== (int) $product->category_id) {
            return false;
        }

        return $this->minimum_order_amount === null
            || $sellerSubtotal >= (float) $this->minimum_order_amount;
    }

    public function discountAmount(float $unitPrice, int $quantity): float
    {
        $lineTotal = round($unitPrice * $quantity, 2);

        $discount = match ($this->type) {
            self::TYPE_PERCENTAGE => $lineTotal * ((float) $this->value / 100),
            self::TYPE_FIXED_AMOUNT => (float) $this->value * $quantity,
            default => 0,
        };

        return round(min($lineTotal, max(0, $discount)), 2);
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [self::TYPE_PERCENTAGE, self::TYPE_FIXED_AMOUNT];
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
    }
}
