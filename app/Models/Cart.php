<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'quantity'];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Users\Buyer::class, 'user_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
