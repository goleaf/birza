<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'title',
        'body',
        'is_approved',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'user_id' => 'integer',
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
