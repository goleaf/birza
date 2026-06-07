<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Dashboard as AdminDashboard;
use App\Models\Activity;
use App\Models\Category;
use App\Models\GlobalSettings;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('backend.dashboard'));

        $response->assertRedirect(route('home'));
    }

    public function test_dashboard_displays_statistics(): void
    {
        $admin = Admin::factory()->create();
        $activity = Activity::factory()->create();
        GlobalSettings::factory()->create([
            'admin_primary_color' => '#112233',
            'admin_accent_color' => '#445566',
            'admin_surface_color' => '#778899',
        ]);
        Category::factory()->count(5)->create();
        Product::factory()->count(10)->create();
        Order::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.dashboard'));

        $response->assertStatus(200)
            ->assertSeeLivewire(AdminDashboard::class)
            ->assertSee(__('backend_spotlight_open'))
            ->assertSee(__('navigation_countries'))
            ->assertSee(__('navigation_attributes'))
            ->assertSee(__('navigation_create_attribute'))
            ->assertSee(__('navigation_profile'))
            ->assertSee(__('navigation_logout'))
            ->assertSee('--admin-primary: #112233', false)
            ->assertSee('--admin-accent: #445566', false)
            ->assertSee('--admin-surface: #778899', false)
            ->assertSee(__('backend_dashboard_activity_item', ['id' => $activity->id]));
    }

    public function test_dashboard_shows_empty_activity_alert_when_no_activity_exists(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.dashboard'));

        $response->assertStatus(200)
            ->assertSee(__('backend_dashboard_recent_activity_empty'));
    }
}
