<?php

namespace Tests\Feature\Marketplace;

use App\Enums\MarketplaceRole;
use App\Livewire\Frontend\Notifications\Index as FrontendNotificationsIndex;
use App\Models\Notification;
use App\Models\User;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class RoleArchitectureStandardTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_private_area_routes_use_strict_role_middleware(): void
    {
        $this->assertRouteMiddleware('buyer.dashboard', [
            'web',
            'auth:buyer',
            'active.account:buyer',
            'verified.account:buyer',
            'buyer.access',
        ]);

        $this->assertRouteMiddleware('seller.dashboard', [
            'web',
            'auth:seller',
            'active.account:seller',
            'verified.account:seller',
            'seller.access',
        ]);

        $this->assertRouteMiddleware('admin.dashboard', [
            'web',
            'auth:admin',
            'active.account:admin',
            'admin.access',
        ]);
    }

    public function test_admin_routes_use_admin_name_prefix_only(): void
    {
        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertFalse(Route::has('backend.dashboard'));
        $this->assertFalse(Route::has('admin.admin.profile'));
        $this->assertTrue(Route::has('admin.profile'));
    }

    public function test_marketplace_role_enum_documents_guards_and_dashboards(): void
    {
        $this->assertSame('buyer', MarketplaceRole::Buyer->guard());
        $this->assertSame('seller', MarketplaceRole::Seller->guard());
        $this->assertSame('admin', MarketplaceRole::Admin->guard());

        $this->assertSame('buyer.dashboard', MarketplaceRole::Buyer->dashboardRoute());
        $this->assertSame('seller.dashboard', MarketplaceRole::Seller->dashboardRoute());
        $this->assertSame('admin.dashboard', MarketplaceRole::Admin->dashboardRoute());

        $this->assertSame(['buyer', 'seller', 'admin'], MarketplaceRole::notificationGuards());
    }

    public function test_one_base_user_can_have_buyer_and_seller_abilities(): void
    {
        $user = User::factory()->create();
        $buyer = Buyer::factory()->forUser($user)->verified()->active()->create();
        $seller = Seller::factory()->forUser($user)->verified()->active()->create();

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.dashboard'))
            ->assertOk();

        $this->actingAs($seller, 'seller')
            ->get(route('seller.dashboard'))
            ->assertOk();
    }

    public function test_inactive_role_accounts_are_logged_out_before_private_area_access(): void
    {
        $buyer = Buyer::factory()->verified()->inactive()->create();
        $seller = Seller::factory()->verified()->inactive()->create();
        $admin = Admin::factory()->inactive()->create();

        $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.dashboard'))
            ->assertRedirect(route('home'));
        $this->assertGuest('buyer');

        $this->actingAs($seller, 'seller')
            ->get(route('seller.dashboard'))
            ->assertRedirect(route('home'));
        $this->assertGuest('seller');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_global_role_gates_are_limited_to_matching_active_role_models(): void
    {
        $admin = Admin::factory()->active()->create();
        $inactiveAdmin = Admin::factory()->inactive()->create();
        $buyer = Buyer::factory()->verified()->active()->create();
        $seller = Seller::factory()->verified()->active()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('accessAdminPanel'));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAdminDashboard'));
        $this->assertFalse(Gate::forUser($inactiveAdmin)->allows('accessAdminPanel'));
        $this->assertFalse(Gate::forUser($buyer)->allows('accessAdminPanel'));

        $this->assertTrue(Gate::forUser($buyer)->allows('accessBuyerCabinet'));
        $this->assertTrue(Gate::forUser($seller)->allows('accessSellerCabinet'));
        $this->assertFalse(Gate::forUser($seller)->allows('accessBuyerCabinet'));
    }

    public function test_notification_read_routes_are_bound_to_the_current_notifiable_owner(): void
    {
        $owner = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $notification = Notification::factory()
            ->forNotifiable($owner)
            ->unread()
            ->create();

        $this->actingAs($otherBuyer, 'buyer')
            ->post(route('buyer.notifications.read', $notification))
            ->assertForbidden();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    public function test_notification_livewire_actions_are_bound_to_the_current_notifiable_owner(): void
    {
        $owner = $this->createBuyer();
        $otherBuyer = $this->createBuyer();
        $notification = Notification::factory()
            ->forNotifiable($owner)
            ->unread()
            ->create();

        Livewire::actingAs($otherBuyer, 'buyer')
            ->test(FrontendNotificationsIndex::class)
            ->call('markAsRead', $notification->id)
            ->assertForbidden();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    /**
     * @param  list<string>  $expectedMiddleware
     */
    private function assertRouteMiddleware(string $routeName, array $expectedMiddleware): void
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "Route [{$routeName}] must exist.");
        $this->assertSame($expectedMiddleware, $route->gatherMiddleware());
    }
}
