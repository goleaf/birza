<?php

namespace App\Actions\Promotions;

use App\Models\PromoCode;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdatePromoCodeAction
{
    public function __construct(
        private readonly RecordPromotionAuditLogsAction $auditLogsAction,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Seller $seller, PromoCode $promoCode, array $attributes): PromoCode
    {
        Gate::forUser($seller)->authorize('update', $promoCode);

        $attributes['code'] = PromoCode::normalizeCode((string) ($attributes['code'] ?? ''));
        $oldValues = $this->auditLogsAction->promoCodeSnapshot($promoCode);
        $promoCode->update($this->validated($promoCode, $attributes));
        $promoCode->refresh();

        $this->auditLogsAction->promoCodeUpdated($seller, $promoCode, $oldValues, 'seller_promo_code_update');

        return $promoCode;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function validated(PromoCode $promoCode, array $attributes): array
    {
        $validator = Validator::make($attributes, $this->rules($promoCode));
        $this->afterValidation($validator);

        return $validator->validate();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(PromoCode $promoCode): array
    {
        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('promo_codes', 'code')->ignore($promoCode)],
            'type' => ['required', Rule::in(PromoCode::types())],
            'value' => ['required', 'numeric', 'gt:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', Rule::in(PromoCode::statuses())],
            'usage_limit' => ['nullable', 'integer', 'min:0'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function afterValidation($validator): void
    {
        $validator->after(function ($validator): void {
            $data = $validator->getData();

            if (($data['type'] ?? null) === PromoCode::TYPE_PERCENTAGE && (float) ($data['value'] ?? 0) > 100) {
                $validator->errors()->add('value', __('promo_codes.validation.percentage_max'));
            }
        });
    }
}
