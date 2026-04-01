<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Livewire\Frontend\Buyer\Dashboard as BuyerDashboard;
use App\Models\Order;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerDashboard::class);
    }
}
