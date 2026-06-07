<?php

namespace App\Models;

use App\Models\Users\Seller;
use Database\Factories\PromoCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PromoCode extends Model
{
    /** @use HasFactory<PromoCodeFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED_AMOUNT = 'fixed_amount';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'seller_id',
        'code',
        'type',
        'value',
        'starts_at',
        'ends_at',
        'status',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'minimum_order_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seller_id' => 'integer',
            'value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'per_user_limit' => 'integer',
            'minimum_order_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $promoCode): void {
            $promoCode->code = self::normalizeCode((string) $promoCode->code);
        });
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
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

    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where('code', self::normalizeCode($code));
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

    public function discountAmount(float $eligibleSubtotal): float
    {
        $discount = match ($this->type) {
            self::TYPE_PERCENTAGE => $eligibleSubtotal * ((float) $this->value / 100),
            self::TYPE_FIXED_AMOUNT => (float) $this->value,
            default => 0,
        };

        return round(min($eligibleSubtotal, max(0, $discount)), 2);
    }

    public static function normalizeCode(string $code): string
    {
        return Str::of($code)
            ->trim()
            ->upper()
            ->replaceMatches('/\s+/', '')
            ->toString();
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
