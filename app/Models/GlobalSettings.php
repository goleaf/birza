<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GlobalSettings extends Model
{
    use HasFactory;

    public const DEFAULT_ADMIN_PRIMARY_COLOR = '#13261F';

    public const DEFAULT_ADMIN_ACCENT_COLOR = '#D2FF72';

    public const DEFAULT_ADMIN_SURFACE_COLOR = '#F4C16D';

    protected $table = 'global_settings';

    protected $fillable = [
        'portal_additional_price',
        'admin_primary_color',
        'admin_accent_color',
        'admin_surface_color',
        'admin_spotlight_tags',
    ];

    public $timestamps = false;

    protected $casts = [
        'portal_additional_price' => 'decimal:2',
        'admin_spotlight_tags' => 'array',
    ];

    public static function adminThemeDefaults(): array
    {
        return [
            'primary' => self::DEFAULT_ADMIN_PRIMARY_COLOR,
            'accent' => self::DEFAULT_ADMIN_ACCENT_COLOR,
            'surface' => self::DEFAULT_ADMIN_SURFACE_COLOR,
        ];
    }

    public function adminThemeColors(): array
    {
        return [
            'primary' => $this->admin_primary_color ?: self::DEFAULT_ADMIN_PRIMARY_COLOR,
            'accent' => $this->admin_accent_color ?: self::DEFAULT_ADMIN_ACCENT_COLOR,
            'surface' => $this->admin_surface_color ?: self::DEFAULT_ADMIN_SURFACE_COLOR,
        ];
    }

    public static function cachedAdminSpotlightTags(): array
    {
        return Cache::remember('admin_spotlight_tags', 60, function (): array {
            return collect(static::query()->first()?->admin_spotlight_tags ?? [])
                ->map(fn (mixed $tag): string => trim((string) $tag))
                ->filter()
                ->values()
                ->all();
        });
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget('portal_additional_price');
            Cache::forget('admin_theme_colors');
            Cache::forget('admin_spotlight_tags');
        });

        static::deleted(function (): void {
            Cache::forget('portal_additional_price');
            Cache::forget('admin_theme_colors');
            Cache::forget('admin_spotlight_tags');
        });
    }
}
