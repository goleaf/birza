<?php

namespace App\Models\Users;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerTransaction;
use App\Models\User;
use Database\Factories\SellerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Seller extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users_sellers';

    protected $fillable = [
        'name',
        'user_id',
        'email',
        'password',
        'company_name',
        'company_code',
        'vat_code',
        'address',
        'phone',
        'veterinary_certificate_number',
        'bank_account',
        'password_reset_at',
        'remember_token',
        'is_verified',
        'is_active',
        'balance',
    ];

    protected $guarded = ['id', 'remember_token', 'email_verified_at'];

    protected $hidden = [
        'password',
        'remember_token',
        'password_reset_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'user_id' => 'integer',
        'password' => 'hashed',
        'password_reset_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
    ];

    protected static function newFactory()
    {
        return SellerFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the seller
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(SellerTransaction::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'seller_categories');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(
            Order::class,
            OrderItem::class,
            'seller_id', // Foreign key on order_items table
            'id', // Foreign key on orders table
            'id', // Local key on sellers table
            'order_id' // Local key on order_items table
        );
    }
}
