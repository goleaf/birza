<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use Tests\TestCase;
use App\Models\Users\Buyer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('buyer.dashboard'));

        $response->assertRedirect(route('home'));
    }

    public function test_dashboard_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();
        Order::factory()->count(3)->create(['buyer_id' => $buyer->id]);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.dashboard'));

        $response->assertStatus(200);
    }
}

