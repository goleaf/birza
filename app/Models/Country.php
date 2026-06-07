<?php

namespace App\Models;

use App\Models\Concerns\HasJsonTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Country extends Model
{
    use HasFactory, HasJsonTranslations;

    public const REGIONS = ['Asia', 'Europe', 'Africa', 'Americas', 'Oceania'];

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
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'country_of_origin');
    }

    public static function getRegionOptions(): array
    {
        return collect(self::REGIONS)
            ->map(fn (string $region) => [
                'label' => __('backend_countries_regions_'.Str::lower($region)),
                'value' => $region,
            ])
            ->all();
    }

    public static function getRegionValues(): array
    {
        return self::REGIONS;
    }

    public function getRegionLabel(): string
    {
        $key = 'backend_countries_regions_'.Str::lower($this->region);
        $label = __($key);

        return $label === $key ? $this->region : $label;
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
