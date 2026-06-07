<?php

namespace App\Actions\Promotions;

use App\Models\Discount;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Services\AuditLogService;
use Illuminate\Contracts\Auth\Authenticatable;

class RecordPromotionAuditLogsAction
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function discountCreated(?Authenticatable $actor, Discount $discount, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'discount.created',
            auditable: $discount,
            oldValues: null,
            newValues: $this->discountSnapshot($discount),
            metadata: ['source' => $source, 'seller_id' => $discount->seller_id],
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function discountUpdated(?Authenticatable $actor, Discount $discount, array $oldValues, string $source): void
    {
        $newValues = $this->discountSnapshot($discount);
        $changed = $this->auditLogService->changedValues($oldValues, $newValues);

        if ($changed['old'] !== [] || $changed['new'] !== []) {
            $this->auditLogService->log(
                actor: $actor,
                action: 'discount.updated',
                auditable: $discount,
                oldValues: $changed['old'],
                newValues: $changed['new'],
                metadata: ['source' => $source, 'seller_id' => $discount->seller_id],
            );
        }

        $this->logStatusChange($actor, $discount, $oldValues['status'] ?? null, $newValues['status'] ?? null, $source);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function discountDeleted(?Authenticatable $actor, Discount $discount, array $oldValues, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'discount.deleted',
            auditable: $discount,
            oldValues: $oldValues,
            newValues: ['deleted_at' => $discount->deleted_at?->toISOString()],
            metadata: ['source' => $source, 'seller_id' => $discount->seller_id],
        );
    }

    public function promoCodeCreated(?Authenticatable $actor, PromoCode $promoCode, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'promo_code.created',
            auditable: $promoCode,
            oldValues: null,
            newValues: $this->promoCodeSnapshot($promoCode),
            metadata: ['source' => $source, 'seller_id' => $promoCode->seller_id],
        );
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function promoCodeUpdated(?Authenticatable $actor, PromoCode $promoCode, array $oldValues, string $source): void
    {
        $newValues = $this->promoCodeSnapshot($promoCode);
        $changed = $this->auditLogService->changedValues($oldValues, $newValues);

        if ($changed['old'] !== [] || $changed['new'] !== []) {
            $this->auditLogService->log(
                actor: $actor,
                action: 'promo_code.updated',
                auditable: $promoCode,
                oldValues: $changed['old'],
                newValues: $changed['new'],
                metadata: ['source' => $source, 'seller_id' => $promoCode->seller_id],
            );
        }

        $this->logStatusChange($actor, $promoCode, $oldValues['status'] ?? null, $newValues['status'] ?? null, $source);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public function promoCodeDeleted(?Authenticatable $actor, PromoCode $promoCode, array $oldValues, string $source): void
    {
        $this->auditLogService->log(
            actor: $actor,
            action: 'promo_code.deleted',
            auditable: $promoCode,
            oldValues: $oldValues,
            newValues: ['deleted_at' => $promoCode->deleted_at?->toISOString()],
            metadata: ['source' => $source, 'seller_id' => $promoCode->seller_id],
        );
    }

    public function promoCodeRedeemed(PromoCodeRedemption $redemption, PromoCode $promoCode, Order $order): void
    {
        $this->auditLogService->log(
            actor: $order->buyer,
            action: 'promo_code.applied_at_checkout',
            auditable: $order,
            oldValues: null,
            newValues: [
                'promo_code_id' => $promoCode->id,
                'promo_code' => $promoCode->code,
                'discount_amount' => $redemption->discount_amount,
                'order_total' => $order->order_total,
            ],
            metadata: [
                'source' => 'checkout',
                'seller_id' => $promoCode->seller_id,
                'redemption_id' => $redemption->id,
            ],
        );

        $this->auditLogService->log(
            actor: $order->buyer,
            action: 'promo_code.redemption_created',
            auditable: $promoCode,
            oldValues: null,
            newValues: [
                'redemption_id' => $redemption->id,
                'user_id' => $redemption->user_id,
                'order_id' => $redemption->order_id,
                'discount_amount' => $redemption->discount_amount,
            ],
            metadata: [
                'source' => 'checkout',
                'seller_id' => $promoCode->seller_id,
                'used_count' => $promoCode->used_count,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function discountSnapshot(Discount $discount): array
    {
        return $this->auditLogService->snapshot($discount, [
            'seller_id',
            'product_id',
            'category_id',
            'name',
            'type',
            'value',
            'starts_at',
            'ends_at',
            'status',
            'usage_limit',
            'used_count',
            'minimum_order_amount',
            'deleted_at',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function promoCodeSnapshot(PromoCode $promoCode): array
    {
        return $this->auditLogService->snapshot($promoCode, [
            'seller_id',
            'code',
            'type',
            'value',
            'starts_at',
            'ends_at',
            'status',
            'usage_limit',
            'used_count',
            'per_user_limit',
            'minimum_order_amount',
            'deleted_at',
        ]);
    }

    private function logStatusChange(
        ?Authenticatable $actor,
        Discount|PromoCode $promotion,
        mixed $oldStatus,
        mixed $newStatus,
        string $source,
    ): void {
        if ($oldStatus === $newStatus) {
            return;
        }

        $prefix = $promotion instanceof Discount ? 'discount' : 'promo_code';
        $action = $newStatus === 'active' ? "{$prefix}.activated" : "{$prefix}.deactivated";

        $this->auditLogService->log(
            actor: $actor,
            action: $action,
            auditable: $promotion,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus],
            metadata: ['source' => $source, 'seller_id' => $promotion->seller_id],
        );
    }
}
