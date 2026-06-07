<?php

namespace Tests\Feature\Feature\Controllers\Backend;

use App\Livewire\Backend\Settings\Index as SettingsIndex;
use App\Models\GlobalSettings;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminThemeSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_theme_settings_save_custom_colors(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(SettingsIndex::class)
            ->set('portal_additional_price', 15.75)
            ->set('admin_primary_color', '#112233')
            ->set('admin_accent_color', '#445566')
            ->set('admin_surface_color', '#778899')
            ->set('admin_spotlight_tags', ['ops', 'catalog'])
            ->call('save')
            ->assertHasNoErrors();

        $settings = GlobalSettings::query()->firstOrFail();

        $this->assertEquals(15.75, (float) $settings->portal_additional_price);
        $this->assertSame('#112233', $settings->admin_primary_color);
        $this->assertSame('#445566', $settings->admin_accent_color);
        $this->assertSame('#778899', $settings->admin_surface_color);
        $this->assertSame(['ops', 'catalog'], $settings->admin_spotlight_tags);
    }
}
