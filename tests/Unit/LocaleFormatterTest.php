<?php

namespace Tests\Unit;

use App\Support\LocaleFormatter;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class LocaleFormatterTest extends TestCase
{
    public function test_currency_uses_current_locale(): void
    {
        app()->setLocale('lt');

        $lithuanian = LocaleFormatter::currency(1234.5);

        app()->setLocale('en');

        $english = LocaleFormatter::currency(1234.5);

        $this->assertStringContainsString('€', $lithuanian);
        $this->assertStringContainsString('€', $english);
        $this->assertNotSame($english, $lithuanian);
    }

    public function test_date_time_uses_locale_and_falls_back_for_null(): void
    {
        app()->setLocale('en');

        $date = CarbonImmutable::parse('2026-06-07 12:30:00', 'Europe/Vilnius');

        $this->assertNotSame('2026-06-07 12:30', LocaleFormatter::dateTime($date));
        $this->assertSame(__('common_not_specified'), LocaleFormatter::dateTime(null));
    }
}
