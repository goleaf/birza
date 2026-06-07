<?php

namespace App\Actions\Promotions;

use App\Models\Cart;
use App\Models\PromoCode;
use App\Models\Users\Buyer;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ValidatePromoCodeAction
{
    /**
     * @param  array<int, array{total_before_promo?: float, total?: float, subtotal?: float}|float>|null  $sellerTotals
     */
    public function handle(Cart $cart, ?Buyer $buyer, string $code, ?array $sellerTotals = null): PromoCode
    {
        $promoCode = PromoCode::query()
            ->with('seller:id,name,company_name,is_active,deleted_at')
            ->code($code)
            ->first();

        if (! $promoCode) {
            $this->fail('promo_code', 'promo_codes.invalid');
        }

        $sellerTotals ??= $this->sellerTotalsFromCart($cart);
        $this->validateForSellerTotals($promoCode, $buyer, $sellerTotals);

        return $promoCode;
    }

    /**
     * @param  array<int, array{total_before_promo?: float, total?: float, subtotal?: float}|float>  $sellerTotals
     */
    public function validateForSellerTotals(PromoCode $promoCode, ?Buyer $buyer, array $sellerTotals): void
    {
        if (! $buyer) {
            $this->fail('promo_code', 'promo_codes.login_required');
        }

        if (! $buyer->is_active || $buyer->trashed()) {
            $this->fail('promo_code', 'promo_codes.buyer_unavailable');
        }

        if (! $promoCode->seller || ! $promoCode->seller->is_active || $promoCode->seller->trashed()) {
            $this->fail('promo_code', 'promo_codes.seller_unavailable');
        }

        if ($promoCode->status !== PromoCode::STATUS_ACTIVE) {
            $this->fail('promo_code', 'promo_codes.inactive');
        }

        if ($promoCode->starts_at && $promoCode->starts_at->isFuture()) {
            $this->fail('promo_code', 'promo_codes.not_started');
        }

        if ($promoCode->ends_at && $promoCode->ends_at->isPast()) {
            $this->fail('promo_code', 'promo_codes.expired');
        }

        if (! $promoCode->hasUsageRemaining()) {
            $this->fail('promo_code', 'promo_codes.usage_limit_reached');
        }

        if ($this->userLimitReached($promoCode, $buyer)) {
            $this->fail('promo_code', 'promo_codes.user_limit_reached');
        }

        $sellerSubtotal = $this->sellerSubtotal($promoCode, $sellerTotals);

        if ($sellerSubtotal <= 0) {
            $this->fail('promo_code', 'promo_codes.not_for_cart');
        }

        if ($promoCode->minimum_order_amount !== null && $sellerSubtotal < (float) $promoCode->minimum_order_amount) {
            throw ValidationException::withMessages([
                'promo_code' => __('promo_codes.minimum_order_amount', [
                    'amount' => number_format((float) $promoCode->minimum_order_amount, 2),
                ]),
            ]);
        }
    }

    public function fail(string $field, string $translationKey): never
    {
        throw ValidationException::withMessages([
            $field => __($translationKey),
        ]);
    }

    /**
     * @return array<int, float>
     */
    private function sellerTotalsFromCart(Cart $cart): array
    {
        return $cart->items()
            ->with('product:id,seller_id,price')
            ->get()
            ->filter(fn ($item): bool => $item->product !== null)
            ->groupBy(fn ($item): int => (int) $item->product->seller_id)
            ->map(fn (Collection $items): float => round(
                (float) $items->sum(fn ($item): float => (float) $item->product->price * (int) $item->quantity),
                2,
            ))
            ->all();
    }

    /**
     * @param  array<int, array{total_before_promo?: float, total?: float, subtotal?: float}|float>  $sellerTotals
     */
    private function sellerSubtotal(PromoCode $promoCode, array $sellerTotals): float
    {
        $sellerTotal = $sellerTotals[(int) $promoCode->seller_id] ?? 0.0;

        if (is_array($sellerTotal)) {
            return (float) ($sellerTotal['total_before_promo'] ?? $sellerTotal['total'] ?? $sellerTotal['subtotal'] ?? 0);
        }

        return (float) $sellerTotal;
    }

    private function userLimitReached(PromoCode $promoCode, Buyer $buyer): bool
    {
        if ($promoCode->per_user_limit === null) {
            return false;
        }

        return $promoCode->redemptions()
            ->where('user_id', $buyer->id)
            ->count() >= $promoCode->per_user_limit;
    }
}
