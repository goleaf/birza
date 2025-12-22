<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\GlobalSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GlobalSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_settings_fillable(): void
    {
        $settings = new GlobalSettings();
        $fillable = $settings->getFillable();

        $this->assertContains('portal_additional_price', $fillable);
    }

    public function test_global_settings_can_be_created(): void
    {
        $settings = GlobalSettings::factory()->create([
            'portal_additional_price' => 10.50,
        ]);

        $this->assertEquals(10.50, $settings->portal_additional_price);
    }
}

