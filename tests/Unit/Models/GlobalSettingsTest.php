<?php

namespace Tests\Unit\Models;

use App\Models\GlobalSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_settings_fillable(): void
    {
        $settings = new GlobalSettings;
        $fillable = $settings->getFillable();

        $this->assertContains('portal_additional_price', $fillable);
        $this->assertContains('admin_spotlight_tags', $fillable);
    }

    public function test_global_settings_can_be_created(): void
    {
        $settings = GlobalSettings::factory()->create([
            'portal_additional_price' => 10.50,
            'admin_spotlight_tags' => ['ops', 'catalog'],
        ]);

        $this->assertEquals(10.50, $settings->portal_additional_price);
        $this->assertSame(['ops', 'catalog'], $settings->admin_spotlight_tags);
    }
}
