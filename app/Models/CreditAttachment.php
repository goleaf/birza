<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_history_id',
        'file_path',
        'original_name',
    ];

    public function creditHistory(): BelongsTo
    {
        return $this->belongsTo(BuyerCreditHistory::class);
    }
}
