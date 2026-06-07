<?php

namespace Tests\Feature\Marketplace;

use App\Actions\Products\RecordProductAuditLogsAction;
use App\Livewire\Backend\AuditLogs\Index as AuditLogsIndex;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLoggingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_audit_action_records_seller_creation_and_price_change(): void
    {
        $seller = Seller::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->create([
            'price' => 10.00,
        ]);

        $recorder = app(RecordProductAuditLogsAction::class);

        $recorder->created($seller, $product, 'seller_product_create');

        $oldValues = $recorder->snapshot($product);
        $product->forceFill([
            'name' => 'Audited Price Update',
            'price' => 12.75,
        ])->save();

        $recorder->updated($seller, $product->refresh(), $oldValues, oldImages: [], source: 'seller_product_edit');

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $seller->id,
            'actor_type' => $seller::class,
            'actor_role' => 'seller',
            'action' => 'product.created',
            'auditable_id' => $product->id,
            'auditable_type' => $product::class,
        ]);

        $priceLog = AuditLog::query()
            ->where('action', 'product.price_changed')
            ->where('auditable_id', $product->id)
            ->firstOrFail();

        $this->assertSame('10.00', number_format((float) $priceLog->old_values['price'], 2, '.', ''));
        $this->assertSame('12.75', number_format((float) $priceLog->new_values['price'], 2, '.', ''));

        $updateLog = AuditLog::query()
            ->where('action', 'product.updated')
            ->where('auditable_id', $product->id)
            ->firstOrFail();

        $this->assertArrayHasKey('name', $updateLog->new_values);
        $this->assertArrayHasKey('price', $updateLog->new_values);
    }

    public function test_checkout_order_and_status_actions_can_be_audited_without_sensitive_payment_data(): void
    {
        $buyer = Buyer::factory()->create();
        $seller = Seller::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->create();
        $order = Order::factory()->for($buyer, 'buyer')->create([
            'order_total' => 25.00,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'quantity' => 2,
            'unit_price' => 12.50,
            'total_price' => 25.00,
        ]);

        $auditLogger = app(AuditLogService::class);

        $auditLogger->log(
            actor: $buyer,
            action: 'order.created',
            auditable: $order,
            newValues: [
                'id' => $order->id,
                'buyer_id' => $buyer->id,
                'order_total' => $order->order_total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'card_number' => '4111111111111111',
            ],
            metadata: [
                'source' => 'checkout',
                'item_count' => 2,
                'seller_ids' => [$seller->id],
            ],
        );

        $auditLogger->log(
            actor: $buyer,
            action: 'cart.checked_out',
            auditable: $order,
            newValues: [
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'total' => $order->order_total,
                'item_count' => 2,
                'seller_ids' => [$seller->id],
            ],
            metadata: ['source' => 'checkout'],
        );

        $auditLogger->log(
            actor: $buyer,
            action: 'order.status_changed',
            auditable: $order,
            oldValues: ['status' => 'pending'],
            newValues: ['status' => 'cancelled'],
            metadata: ['source' => 'order_status_action'],
            reason: 'Buyer requested cancellation.',
        );

        $orderLog = AuditLog::query()
            ->where('action', 'order.created')
            ->firstOrFail();

        $this->assertSame($buyer->id, $orderLog->actor_id);
        $this->assertSame('buyer', $orderLog->actor_role);
        $this->assertSame(2, $orderLog->metadata['item_count']);
        $this->assertSame([$seller->id], $orderLog->metadata['seller_ids']);
        $this->assertArrayNotHasKey('card_number', $orderLog->new_values);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'cart.checked_out',
            'auditable_id' => $order->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.status_changed',
            'auditable_id' => $order->id,
            'reason' => 'Buyer requested cancellation.',
        ]);
    }

    public function test_admin_account_decision_audit_log_stores_reason_and_actor_role(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create(['is_active' => true]);

        app(AuditLogService::class)->log(
            actor: $admin,
            action: 'user.blocked',
            auditable: $buyer,
            oldValues: ['is_active' => true],
            newValues: ['is_active' => false],
            metadata: ['source' => 'admin_buyer_form'],
            reason: 'Fraud review.',
        );

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'actor_role' => 'admin',
            'action' => 'user.blocked',
            'auditable_id' => $buyer->id,
            'auditable_type' => $buyer::class,
            'reason' => 'Fraud review.',
        ]);
    }

    public function test_admin_audit_page_access_is_restricted(): void
    {
        $admin = Admin::factory()->create(['is_active' => true]);
        $buyer = Buyer::factory()->create();
        $seller = Seller::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('backend.audit.index'))
            ->assertOk();

        auth('admin')->logout();

        $this->actingAs($buyer, 'buyer')
            ->get(route('backend.audit.index'))
            ->assertRedirect(route('home'));

        auth('buyer')->logout();

        $this->actingAs($seller, 'seller')
            ->get(route('backend.audit.index'))
            ->assertRedirect(route('home'));
    }

    public function test_audit_log_filters_work_on_admin_page(): void
    {
        $admin = Admin::factory()->create(['is_active' => true]);
        $product = Product::factory()->create();

        AuditLog::factory()
            ->byAdmin($admin)
            ->forAuditable($product)
            ->action('product.created')
            ->create();

        AuditLog::factory()
            ->byAdmin($admin)
            ->forAuditable($product)
            ->action('order.created')
            ->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(AuditLogsIndex::class)
            ->set('action', 'product.created')
            ->assertSee('product.created')
            ->assertViewHas('logs', fn ($logs): bool => $logs->count() === 1
                && $logs->first()->action === 'product.created');
    }
}
