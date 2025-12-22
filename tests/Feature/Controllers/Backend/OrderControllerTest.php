<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.orders.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_order_index_displays_orders(): void
    {
        $admin = Admin::factory()->create();
        Order::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.orders.index'));

        $response->assertStatus(200);
    }
}

