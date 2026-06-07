<?php

namespace App\Actions\Promotions;

use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPromoCodeRedemptionAction
{
    public function __construct(
        private readonly ValidatePromoCodeAction $validatePromoCodeAction,
        private readonly RecordPromotionAuditLogsAction $auditLogsAction,
    ) {}

    public function handle(
        PromoCode $promoCode,
        Buyer $buyer,
        Order $order,
        float $discountAmount,
        float $eligibleSubtotal,
    ): PromoCodeRedemption {
        if ($discountAmount <= 0) {
            throw ValidationException::withMessages([
                'promo_code' => __('promo_codes.invalid_discount_amount'),
            ]);
        }

        return DB::transaction(function () use ($promoCode, $buyer, $order, $discountAmount, $eligibleSubtotal): PromoCodeRedemption {
            $lockedPromoCode = PromoCode::query()
                ->with('seller')
                ->lockForUpdate()
                ->findOrFail($promoCode->id);

            $this->validatePromoCodeAction->validateForSellerTotals($lockedPromoCode, $buyer, [
                (int) $lockedPromoCode->seller_id => $eligibleSubtotal,
            ]);

            $redemption = PromoCodeRedemption::query()->create([
                'promo_code_id' => $lockedPromoCode->id,
                'user_id' => $buyer->id,
                'order_id' => $order->id,
                'discount_amount' => round($discountAmount, 2),
            ]);

            $lockedPromoCode->increment('used_count');
            $lockedPromoCode->refresh();

            $this->auditLogsAction->promoCodeRedeemed($redemption, $lockedPromoCode, $order->fresh(['buyer']) ?? $order);

            return $redemption;
        });
    }
}
