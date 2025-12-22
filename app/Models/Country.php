<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasJsonTranslations;

class Country extends Model
{
    use HasJsonTranslations, HasFactory;

    protected $table = 'countries';

    protected $fillable = [
        'alpha2',
        'region',
        'is_active',
        'country_name',
        'description',
    ];

    public $translatable = [
        'country_name',
        'description',
    ];

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'country_of_origin');
    }

    public static function getRegionOptions(): array
    {
        return ['Asia', 'Europe', 'Africa', 'Americas', 'Oceania'];
    }

    public function getFallbackLocale(): string
    {
        return config('app.fallback_locale');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}
