<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Settings\Index as SettingsIndex;
use App\Models\GlobalSettings;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_index_requires_authentication(): void
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_settings_index_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        GlobalSettings::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.settings.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(SettingsIndex::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_global_settings'))
            ->assertSee(__('backend_settings_fields_admin_primary_color'))
            ->assertSee(__('backend_settings_fields_admin_accent_color'))
            ->assertSee(__('backend_settings_fields_admin_surface_color'))
            ->assertSee(__('backend_settings_fields_admin_spotlight_tags'))
            ->assertSee(__('backend_settings_spotlight_tags_hint'));
    }
}
