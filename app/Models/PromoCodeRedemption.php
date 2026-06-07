<?php

namespace App\Models;

use App\Models\Users\Buyer;
use Database\Factories\PromoCodeRedemptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCodeRedemption extends Model
{
    /** @use HasFactory<PromoCodeRedemptionFactory> */
    use HasFactory;

    protected $fillable = [
        'promo_code_id',
        'user_id',
        'order_id',
        'discount_amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'promo_code_id' => 'integer',
            'user_id' => 'integer',
            'order_id' => 'integer',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
