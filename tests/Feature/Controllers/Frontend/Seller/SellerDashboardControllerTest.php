<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use App\Livewire\Frontend\Seller\Dashboard as SellerDashboard;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('seller.dashboard'));

        $response->assertRedirect(route('home'));
    }

    public function test_dashboard_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create([
            'company_name' => 'Seller Hub',
        ]);
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $order = Order::factory()->create([
            'payment_status' => Order::STATUS['PENDING'],
        ]);
        OrderItem::factory()->create([
            'seller_id' => $seller->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.dashboard'));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerDashboard::class)
            ->assertSee(__('auth_company_name'))
            ->assertSee(__('dashboard_company'))
            ->assertSee(route('seller.profile.edit', ['tab' => 'categories-tab']))
            ->assertSee(__('dashboard_profit_factor'))
            ->assertSee(__('dashboard_monthly_sales_subtitle'))
            ->assertSee('badge-warning')
            ->assertSee('chart.umd.min.js')
            ->assertSee('<canvas x-ref="chart"></canvas>', false)
            ->assertSee('mask-circle', false)
            ->assertSee('rating-sm', false)
            ->assertSee('radial-progress', false)
            ->assertSee('progress-warning', false)
            ->assertSee('progress-error', false)
            ->assertSee('font-black text-xl', false);
    }
}
