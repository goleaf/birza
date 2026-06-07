<?php

namespace Tests\Feature\Security;

use App\Actions\Audit\RecordAdminAction;
use App\Livewire\Backend\Products\Index as AdminProductsIndex;
use App\Livewire\Frontend\Seller\Products\Edit as SellerProductEdit;
use App\Livewire\Frontend\Seller\Products\Index as SellerProductsIndex;
use App\Models\AdminAction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_private_dashboards(): void
    {
        $this->get(route('buyer.dashboard'))->assertRedirect(route('home'));
        $this->get(route('seller.dashboard'))->assertRedirect(route('home'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('home'));
    }

    public function test_cross_role_manual_urls_are_blocked(): void
    {
        $buyer = Buyer::factory()->verified()->active()->create();
        $seller = Seller::factory()->verified()->active()->create();

        $this->actingAs($buyer, 'buyer')
            ->get(route('seller.dashboard'))
            ->assertRedirect(route('home'));

        $this->actingAs($seller, 'seller')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'));
    }

    public function test_unverified_seller_is_logged_out_from_private_area(): void
    {
        $seller = Seller::factory()->unverified()->active()->create();

        $this->actingAs($seller, 'seller')
            ->get(route('seller.dashboard'))
            ->assertRedirect(route('home'));

        $this->assertGuest('seller');
    }

    public function test_seller_cannot_open_or_mutate_another_sellers_product(): void
    {
        $seller = Seller::factory()->verified()->active()->create();
        $otherSeller = Seller::factory()->verified()->active()->create();
        $otherProduct = Product::factory()->for($otherSeller, 'seller')->create();

        Livewire::actingAs($seller, 'seller')
            ->test(SellerProductEdit::class, ['product' => $otherProduct])
            ->assertForbidden();

        Livewire::actingAs($seller, 'seller')
            ->test(SellerProductsIndex::class)
            ->call('softDeleteProduct', $otherProduct->id)
            ->assertForbidden();
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        $buyer = Buyer::factory()->verified()->active()->create();
        $otherBuyer = Buyer::factory()->verified()->active()->create();
        $order = Order::factory()->for($otherBuyer, 'buyer')->create();

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.orders.show', $order))
            ->assertForbidden();
    }

    public function test_admin_product_delete_creates_admin_action(): void
    {
        $admin = Admin::factory()->active()->create();
        $product = Product::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(AdminProductsIndex::class)
            ->set('auditReason', 'Duplicate unsafe listing.')
            ->call('deleteProduct', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('admin_actions', [
            'actor_user_id' => $admin->id,
            'action' => 'product.deleted',
            'entity_type' => Product::class,
            'entity_id' => $product->id,
            'reason' => 'Duplicate unsafe listing.',
        ]);
    }

    public function test_mass_assignment_cannot_change_protected_marketplace_fields(): void
    {
        $product = new Product;
        $order = new Order;
        $buyer = new Buyer;

        $this->silentlyDiscardProtectedFill(fn (): Product => $product->fill([
            'seller_id' => 999,
            'is_active' => false,
            'product_image' => 'unsafe.jpg',
        ]));

        $this->silentlyDiscardProtectedFill(fn (): Order => $order->fill([
            'buyer_id' => 999,
            'status' => 'completed',
            'order_total' => 1,
        ]));

        $this->silentlyDiscardProtectedFill(fn (): Buyer => $buyer->fill([
            'credit_balance' => 999,
            'is_active' => false,
            'is_verified' => false,
        ]));

        $this->assertNull($product->seller_id);
        $this->assertNull($product->is_active);
        $this->assertNull($product->product_image);
        $this->assertNull($order->buyer_id);
        $this->assertNull($order->status);
        $this->assertNull($order->order_total);
        $this->assertNull($buyer->credit_balance);
        $this->assertNull($buyer->is_active);
        $this->assertNull($buyer->is_verified);
    }

    public function test_admin_action_redacts_sensitive_values(): void
    {
        $admin = Admin::factory()->active()->create();

        app(RecordAdminAction::class)->handle(
            actor: $admin,
            action: 'settings.updated',
            newValues: [
                'email' => 'admin@example.test',
                'password' => 'secret',
                'api_token' => 'secret-token',
            ],
        );

        $action = AdminAction::query()->firstOrFail();

        $this->assertSame('admin@example.test', $action->new_values['email']);
        $this->assertArrayNotHasKey('password', $action->new_values);
        $this->assertArrayNotHasKey('api_token', $action->new_values);
    }

    private function silentlyDiscardProtectedFill(callable $callback): void
    {
        try {
            $callback();
        } catch (MassAssignmentException) {
            //
        }
    }
}
