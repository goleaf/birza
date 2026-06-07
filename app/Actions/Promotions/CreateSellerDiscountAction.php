<?php

namespace App\Actions\Promotions;

use App\Models\Discount;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateSellerDiscountAction
{
    public function __construct(
        private readonly RecordPromotionAuditLogsAction $auditLogsAction,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Seller $seller, array $attributes): Discount
    {
        Gate::forUser($seller)->authorize('create', Discount::class);

        $validated = $this->validated($seller, $attributes);
        $discount = $seller->discounts()->create($validated);

        $this->auditLogsAction->discountCreated($seller, $discount, 'seller_discount_create');

        return $discount;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function validated(Seller $seller, array $attributes): array
    {
        $validator = Validator::make($attributes, $this->rules($seller));
        $this->afterValidation($validator);

        return $validator->validate();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(Seller $seller): array
    {
        return [
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('seller_id', $seller->id)],
            'category_id' => ['nullable', 'integer', Rule::exists('seller_categories', 'category_id')->where('seller_id', $seller->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(Discount::types())],
            'value' => ['required', 'numeric', 'gt:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'status' => ['required', Rule::in(Discount::statuses())],
            'usage_limit' => ['nullable', 'integer', 'min:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function afterValidation($validator): void
    {
        $validator->after(function ($validator): void {
            $data = $validator->getData();

            if (($data['type'] ?? null) === Discount::TYPE_PERCENTAGE && (float) ($data['value'] ?? 0) > 100) {
                $validator->errors()->add('value', __('discounts.validation.percentage_max'));
            }

            if (filled($data['product_id'] ?? null) && filled($data['category_id'] ?? null)) {
                $validator->errors()->add('product_id', __('discounts.validation.single_target'));
            }
        });
    }
}
