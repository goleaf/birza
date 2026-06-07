<?php

namespace App\Models\Users;

use App\Models\ProductQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $table = 'users_admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\AdminFactory::new();
    }

    public function moderatedProductQuestions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class, 'moderated_by_admin_id');
    }
}
