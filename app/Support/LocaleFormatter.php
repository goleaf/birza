<?php

namespace App\Support;

use Carbon\CarbonInterface;
use IntlDateFormatter;
use NumberFormatter;

class LocaleFormatter
{
    public static function currency(float|int|string|null $amount, string $currency = 'EUR', ?string $locale = null): string
    {
        $locale = self::intlLocale($locale);
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency((float) $amount, $currency) ?: number_format((float) $amount, 2).' '.$currency;
    }

    public static function dateTime(?CarbonInterface $date, ?string $locale = null): string
    {
        if ($date === null) {
            return __('common_not_specified');
        }

        $formatter = new IntlDateFormatter(
            self::intlLocale($locale),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT,
            $date->timezoneName,
        );

        return $formatter->format($date->getTimestamp()) ?: $date->format('Y-m-d H:i');
    }

    private static function intlLocale(?string $locale = null): string
    {
        return match ($locale ?? app()->getLocale()) {
            'lt' => 'lt_LT',
            'en' => 'en_US',
            default => str_replace('-', '_', (string) ($locale ?? config('app.fallback_locale'))),
        };
    }
}
