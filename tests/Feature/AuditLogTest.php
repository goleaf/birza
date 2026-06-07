<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_service_stores_actor_role_entity_reason_and_request_context(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create();
        $request = Request::create('/admin/products/'.$product->id, 'POST', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Audit Test Agent',
        ]);

        $log = app(AuditLogService::class)->log(
            actor: $admin,
            action: 'product.updated',
            auditable: $product,
            oldValues: ['price' => '10.00'],
            newValues: ['price' => '12.50'],
            metadata: ['source' => 'test'],
            reason: 'Price correction.',
            request: $request,
        );

        $this->assertSame($admin->id, $log->actor_id);
        $this->assertSame($admin::class, $log->actor_type);
        $this->assertSame('admin', $log->actor_role);
        $this->assertSame($product->id, $log->auditable_id);
        $this->assertSame($product::class, $log->auditable_type);
        $this->assertSame(['price' => '10.00'], $log->old_values);
        $this->assertSame(['price' => '12.50'], $log->new_values);
        $this->assertSame('Price correction.', $log->reason);
        $this->assertSame('203.0.113.10', $log->ip_address);
        $this->assertSame('Audit Test Agent', $log->user_agent);
    }

    public function test_audit_log_sanitizes_sensitive_values_recursively(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create();

        $log = app(AuditLogService::class)->log(
            actor: $admin,
            action: 'product.updated',
            auditable: $product,
            oldValues: [
                'password' => 'secret',
                'safe' => 'kept',
                'nested' => [
                    'api_token' => 'token',
                    'price' => '10.00',
                ],
            ],
            newValues: [
                'remember_token' => 'hidden',
                'nested' => [
                    'card_number' => '4111111111111111',
                    'price' => '12.50',
                ],
            ],
            metadata: [
                'authorization' => 'Bearer token',
                'source' => 'test',
            ],
        );

        $this->assertArrayNotHasKey('password', $log->old_values);
        $this->assertArrayNotHasKey('api_token', $log->old_values['nested']);
        $this->assertArrayNotHasKey('remember_token', $log->new_values);
        $this->assertArrayNotHasKey('card_number', $log->new_values['nested']);
        $this->assertArrayNotHasKey('authorization', $log->metadata);
        $this->assertSame('kept', $log->old_values['safe']);
        $this->assertSame('12.50', $log->new_values['nested']['price']);
    }

    public function test_audit_log_scopes_filter_by_action_actor_entity_role_and_date(): void
    {
        $admin = Admin::factory()->create();
        $product = Product::factory()->create();

        $matching = AuditLog::factory()
            ->byAdmin($admin)
            ->forAuditable($product)
            ->action('product.price_changed')
            ->create([
                'created_at' => now()->subDay(),
            ]);

        AuditLog::factory()->action('order.created')->create();

        $logs = AuditLog::query()
            ->action('product.price_changed')
            ->actor($admin)
            ->entity($product)
            ->role('admin')
            ->createdFrom(now()->subDays(2))
            ->createdUntil(now())
            ->get();

        $this->assertCount(1, $logs);
        $this->assertSame($matching->id, $logs->first()->id);
    }
}
