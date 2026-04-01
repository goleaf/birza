<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Dashboard as AdminDashboard;
use App\Models\Category;
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
        Category::factory()->count(5)->create();
        Product::factory()->count(10)->create();
        Order::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.dashboard'));

        $response->assertStatus(200)
            ->assertSeeLivewire(AdminDashboard::class);
    }
}
