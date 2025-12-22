<?php

namespace App\Models;

use App\Models\CreditAttachment;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function attachments()
    {
        return $this->hasMany(CreditAttachment::class, 'credit_history_id');
    }
}
