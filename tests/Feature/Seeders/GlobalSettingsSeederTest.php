<?php

namespace Tests\Feature\Seeders;

use App\Models\GlobalSettings;
use Database\Seeders\test_information\GlobalSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSettingsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_settings_seeder_uses_a_deterministic_default_value(): void
    {
        $this->seed(GlobalSettingsSeeder::class);

        $this->assertSame(0.0, (float) GlobalSettings::query()->value('portal_additional_price'));

        GlobalSettings::query()->update([
            'portal_additional_price' => 25,
        ]);

        $this->seed(GlobalSettingsSeeder::class);

        $this->assertSame(0.0, (float) GlobalSettings::query()->value('portal_additional_price'));
    }
}
