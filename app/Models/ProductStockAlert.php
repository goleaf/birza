<?php

namespace App\Models;

use App\Enums\ProductStockAlertStatus;
use App\Models\Users\Buyer;
use Database\Factories\ProductStockAlertFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductStockAlert extends Model
{
    /** @use HasFactory<ProductStockAlertFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'buyer_id',
        'status',
        'notified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'buyer_id' => 'integer',
            'status' => ProductStockAlertStatus::class,
            'notified_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProductStockAlertStatus::Active->value);
    }

    public function scopeNotified(Builder $query): Builder
    {
        return $query->where('status', ProductStockAlertStatus::Notified->value);
    }

    public function scopeForBuyer(Builder $query, Buyer $buyer): Builder
    {
        return $query->where('buyer_id', $buyer->id);
    }

    public function isActive(): bool
    {
        return $this->status === ProductStockAlertStatus::Active;
    }

    public function markNotified(): void
    {
        $this->forceFill([
            'status' => ProductStockAlertStatus::Notified,
            'notified_at' => now(),
        ])->save();
    }

    public function cancel(): void
    {
        $this->forceFill([
            'status' => ProductStockAlertStatus::Cancelled,
        ])->save();
    }
}
