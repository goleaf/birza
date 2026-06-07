<?php

namespace App\Models;

use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuyerCreditHistory extends Model
{
    use HasFactory;

    protected $table = 'buyer_credit_history';

    protected $fillable = [
        'buyer_id',
        'amount',
        'type',
        'balance_after',
        'admin_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CreditAttachment::class, 'credit_history_id');
    }
}
