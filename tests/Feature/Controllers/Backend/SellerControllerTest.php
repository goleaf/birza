<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Sellers\Form as SellerForm;
use App\Livewire\Backend\Sellers\Index as SellerIndex;
use App\Livewire\Backend\Sellers\Orders as SellerOrdersPage;
use App\Livewire\Backend\Sellers\Show as SellerShow;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SellerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.sellers.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_seller_index_displays_sellers(): void
    {
        $admin = Admin::factory()->create();
        Seller::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.sellers.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerIndex::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_sellers'))
            ->assertSee(__('common_actions'))
            ->assertSee(__('common_view'))
            ->assertSee(__('common_edit'))
            ->assertSee(__('sellers_orders_list'))
            ->assertSee(__('common_delete'));
    }

    public function test_seller_create_form_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.sellers.create'));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_sellers'))
            ->assertSee(__('backend_sellers_fields_password'))
            ->assertSee(__('backend_sellers_fields_is_verified'))
            ->assertSee(__('backend_sellers_fields_is_active'));
    }

    public function test_seller_edit_form_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $seller = Seller::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.sellers.edit', $seller));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_sellers'))
            ->assertSee(__('backend_sellers_fields_is_verified'))
            ->assertSee(__('backend_sellers_fields_is_active'));
    }

    public function test_seller_show_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $seller = Seller::factory()->create([
            'company_name' => 'Nordic Harvest',
            'email' => 'seller@example.com',
        ]);
        $buyer = Buyer::factory()->create([
            'company_name' => 'Market Buyer',
        ]);
        $product = Product::factory()->for($seller, 'seller')->create([
            'name' => 'Fresh Apples',
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create();

        OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($seller, 'seller')
            ->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.sellers.show', $seller));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerShow::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_sellers'))
            ->assertSee('Nordic Harvest')
            ->assertSee('seller@example.com')
            ->assertSee('Fresh Apples')
            ->assertSee('Market Buyer');
    }

    public function test_seller_show_displays_status_and_empty_state_alerts(): void
    {
        $admin = Admin::factory()->create();
        $seller = Seller::factory()->inactive()->create([
            'is_verified' => false,
            'company_name' => 'Dormant Supplier',
            'email' => 'dormant@example.com',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.sellers.show', $seller));

        $response->assertStatus(200)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('backend_sellers_show_inactive_alert'))
            ->assertSee(__('backend_sellers_show_unverified_title'))
            ->assertSee(__('backend_sellers_show_unverified_alert'))
            ->assertSee(__('products_no_products'))
            ->assertSee(__('orders_no_orders'));
    }

    public function test_seller_show_limits_recent_orders_and_does_not_eager_load_order_items(): void
    {
        $admin = Admin::factory()->create();
        $seller = Seller::factory()->create();
        $buyer = Buyer::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->create();

        $orders = Order::factory()
            ->count(12)
            ->for($buyer, 'buyer')
            ->sequence(fn ($sequence) => [
                'created_at' => now()->subMinutes(12 - $sequence->index),
                'order_total' => match ($sequence->index) {
                    0 => 1111.11,
                    11 => 9999.99,
                    default => 50,
                },
            ])
            ->create();

        foreach ($orders as $order) {
            OrderItem::factory()
                ->for($order)
                ->for($product)
                ->for($seller, 'seller')
                ->create();
        }

        DB::enableQueryLog();

        try {
            $response = $this->actingAs($admin, 'admin')
                ->get(route('backend.sellers.show', $seller));

            $orderItemEagerLoads = collect(DB::getQueryLog())
                ->pluck('query')
                ->filter(fn (string $query): bool => str_contains(
                    strtolower($query),
                    'from "order_items" where "order_items"."order_id" in'
                ));

            $response->assertOk()
                ->assertSee('9,999.99 €')
                ->assertDontSee('1,111.11 €');
            $this->assertEmpty($orderItemEagerLoads->all());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_seller_orders_display_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $seller = Seller::factory()->create([
            'company_name' => 'Nordic Harvest',
        ]);
        $buyer = Buyer::factory()->create([
            'company_name' => 'Market Buyer',
            'email' => 'buyer@example.com',
        ]);
        $product = Product::factory()->for($seller, 'seller')->create([
            'name' => 'Fresh Apples',
        ]);
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'payment_status' => 'paid',
        ]);

        OrderItem::factory()
            ->for($order)
            ->for($product)
            ->for($seller, 'seller')
            ->create([
                'quantity' => 2,
                'unit_price' => 12.5,
            ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.sellers.orders', $seller));

        $response->assertStatus(200)
            ->assertSeeLivewire(SellerOrdersPage::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_sellers'))
            ->assertSee(__('common_orders'))
            ->assertSee('Nordic Harvest')
            ->assertSee('Market Buyer')
            ->assertSee('Fresh Apples')
            ->assertSee('buyer@example.com');
    }
}
