<?php

namespace App\Actions\Orders;

use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Enums\OrderStatusActorRole;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Services\AuditLogService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class CreateOrderEventAction
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function handle(
        Order $order,
        OrderEventType $eventType,
        ?Authenticatable $actor = null,
        ?OrderStatus $oldStatus = null,
        ?OrderStatus $newStatus = null,
        ?string $publicNote = null,
        ?string $internalNote = null,
        ?array $metadata = null,
        ?CarbonInterface $createdAt = null,
    ): OrderEvent {
        $role = OrderStatusActorRole::fromActor($actor);

        return $order->events()->create([
            'actor_id' => $actor ? (int) $actor->getAuthIdentifier() : null,
            'actor_role' => $role,
            'event_type' => $eventType,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'title_key' => $eventType->titleKey(),
            'description_key' => $eventType->descriptionKey(),
            'public_note' => $this->cleanText($publicNote),
            'internal_note' => $this->cleanText($internalNote),
            'metadata' => $this->auditLogService->sanitize($metadata) ?? [],
            'created_at' => $createdAt ?? now(),
        ]);
    }

    private function cleanText(?string $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : $value;
    }
}
