<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        $response->assertStatus(200);
    }
}

