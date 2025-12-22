<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\GlobalSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GlobalSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.settings.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_settings_index_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        GlobalSettings::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.settings.index'));

        $response->assertStatus(200);
    }
}

