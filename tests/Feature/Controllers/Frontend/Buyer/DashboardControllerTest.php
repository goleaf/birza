<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Enums\OrderPaymentStatus;
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
        $buyer = Buyer::factory()->create([
            'company_name' => 'Buyer Hub',
        ]);
        Order::factory()->count(3)->create([
            'buyer_id' => $buyer->id,
            'payment_status' => OrderPaymentStatus::Pending,
        ]);

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.dashboard'));

        $response->assertStatus(200)
            ->assertSeeLivewire(BuyerDashboard::class)
            ->assertSee('badge-warning')
            ->assertSee(__('dashboard_banners'))
            ->assertSee(__('auth_company_name'))
            ->assertSee(__('product_search_list'))
            ->assertSee(__('dashboard_sales_performance_subtitle'))
            ->assertSee('chart.umd.min.js')
            ->assertSee('<canvas x-ref="chart"></canvas>', false)
            ->assertSee('aria-label="slides"', false);
    }
}
