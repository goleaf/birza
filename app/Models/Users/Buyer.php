<?php

namespace App\Models\Users;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Order;
use App\Models\BuyerCreditHistory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Buyer extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users_buyers';

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_name',
        'company_code',
        'vat_code',
        'address',
        'phone',
        'bank_account',
        'credit_balance',
        'password_reset_at',
        'remember_token',
        'is_verified',
        'is_active',
    ];

    protected $guarded = ['id', 'remember_token', 'email_verified_at'];

    protected $hidden = [
        'password',
        'remember_token',
        'password_reset_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_reset_at' => 'datetime',
        'credit_balance' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function creditHistory(): HasMany
    {
        return $this->hasMany(BuyerCreditHistory::class, 'buyer_id');
    }

    public function addCredit(float $amount, ?int $adminId = null, ?string $note = null): void
    {
        $this->increment('credit_balance', $amount);
        $this->refresh();

        BuyerCreditHistory::create([
            'buyer_id' => $this->id,
            'amount' => $amount,
            'type' => 'add',
            'balance_after' => $this->credit_balance,
            'admin_id' => $adminId,
            'note' => $note,
        ]);
    }

    public function deductCredit(float $amount, ?int $adminId = null, ?string $note = null): bool
    {
        if ($this->credit_balance >= $amount) {
            $this->decrement('credit_balance', $amount);
            $this->refresh();

            BuyerCreditHistory::create([
                'buyer_id' => $this->id,
                'amount' => $amount,
                'type' => 'deduct',
                'balance_after' => $this->credit_balance,
                'admin_id' => $adminId,
                'note' => $note,
            ]);
            return true;
        }
        return false;
    }
}
