<?php

namespace App\Models\Users;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SellerTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Seller extends Authenticatable
{
    use SoftDeletes, HasFactory, Notifiable;

    protected $table = 'users_sellers';

    protected $fillable = [
        'name',
        'email',
        'password',
        'company_name',
        'company_code',
        'vat_code',
        'address',
        'phone',
        'veterinary_certificate_number',
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
        'password' => 'hashed',
        'password_reset_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\SellerFactory::new();
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

    public function orders()
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

    public function scopeWithTrashed($query)
    {
        return $query->withTrashed();
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->onlyTrashed();
    }

}
