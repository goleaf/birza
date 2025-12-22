<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditAttachment extends Model
{
    protected $fillable = [
        'credit_history_id',
        'file_path',
        'original_name'
    ];

    public function creditHistory()
    {
        return $this->belongsTo(BuyerCreditHistory::class);
    }
}
