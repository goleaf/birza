<?php

namespace Database\Seeders\Demo;

use App\Models\Discount;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Users\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoPromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('discounts') || ! Schema::hasTable('promo_codes')) {
            return;
        }

        $this->ensureDemoDependencies();

        $sellerOne = $this->seller('demo-seller-one@example.com');
        $sellerTwo = $this->seller('demo-seller-two@example.com');
        $apples = $this->product('Demo Active Apples');
        $bread = $this->product('Demo Seller Two Bread');

        $this->discount($sellerOne, 'Demo Apples Percentage Discount', [
            'product_id' => $apples->id,
            'category_id' => null,
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => 10,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => Discount::STATUS_ACTIVE,
            'usage_limit' => 100,
            'used_count' => 0,
            'minimum_order_amount' => 10,
        ]);

        $this->discount($sellerOne, 'Demo Category Fixed Discount', [
            'product_id' => null,
            'category_id' => $apples->category_id,
            'type' => Discount::TYPE_FIXED_AMOUNT,
            'value' => 1.50,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeeks(2),
            'status' => Discount::STATUS_ACTIVE,
            'usage_limit' => null,
            'used_count' => 0,
            'minimum_order_amount' => null,
        ]);

        $this->discount($sellerTwo, 'Demo Inactive Bread Discount', [
            'product_id' => $bread->id,
            'category_id' => null,
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => 15,
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->addWeek(),
            'status' => Discount::STATUS_INACTIVE,
            'usage_limit' => 25,
            'used_count' => 0,
            'minimum_order_amount' => null,
        ]);

        $this->promoCode($sellerOne, 'SAVE10', [
            'type' => PromoCode::TYPE_PERCENTAGE,
            'value' => 10,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => PromoCode::STATUS_ACTIVE,
            'usage_limit' => 100,
            'used_count' => 0,
            'per_user_limit' => 1,
            'minimum_order_amount' => 20,
        ]);

        $this->promoCode($sellerOne, 'EXPIRED', [
            'type' => PromoCode::TYPE_PERCENTAGE,
            'value' => 25,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
            'status' => PromoCode::STATUS_ACTIVE,
            'usage_limit' => 50,
            'used_count' => 0,
            'per_user_limit' => 1,
            'minimum_order_amount' => null,
        ]);

        $this->promoCode($sellerOne, 'ALMOSTUSED', [
            'type' => PromoCode::TYPE_FIXED_AMOUNT,
            'value' => 3,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'status' => PromoCode::STATUS_ACTIVE,
            'usage_limit' => 5,
            'used_count' => 4,
            'per_user_limit' => 1,
            'minimum_order_amount' => null,
        ]);

        $this->promoCode($sellerTwo, 'SELLER2ONLY', [
            'type' => PromoCode::TYPE_FIXED_AMOUNT,
            'value' => 2,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => PromoCode::STATUS_ACTIVE,
            'usage_limit' => 100,
            'used_count' => 0,
            'per_user_limit' => 1,
            'minimum_order_amount' => 10,
        ]);

        $this->promoCode($sellerTwo, 'HIGHMIN', [
            'type' => PromoCode::TYPE_PERCENTAGE,
            'value' => 20,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => PromoCode::STATUS_ACTIVE,
            'usage_limit' => 100,
            'used_count' => 0,
            'per_user_limit' => 1,
            'minimum_order_amount' => 500,
        ]);
    }

    private function seller(string $email): Seller
    {
        return Seller::query()->where('email', $email)->firstOrFail();
    }

    private function product(string $name): Product
    {
        return Product::query()->where('name', $name)->firstOrFail();
    }

    private function ensureDemoDependencies(): void
    {
        if (! Seller::query()->where('email', 'demo-seller-one@example.com')->exists()) {
            $this->call(DemoUserSeeder::class);
        }

        if (! Product::query()->where('name', 'Demo Active Apples')->exists()) {
            $this->call(DemoCatalogSeeder::class);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function discount(Seller $seller, string $name, array $attributes): Discount
    {
        $discount = Discount::withTrashed()->firstOrNew([
            'seller_id' => $seller->id,
            'name' => $name,
        ]);

        $discount->forceFill(array_merge($attributes, [
            'seller_id' => $seller->id,
            'name' => $name,
        ]));

        if ($discount->trashed()) {
            $discount->restore();
        }

        $discount->save();

        return $discount;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function promoCode(Seller $seller, string $code, array $attributes): PromoCode
    {
        $promoCode = PromoCode::withTrashed()->firstOrNew([
            'code' => PromoCode::normalizeCode($code),
        ]);

        $promoCode->forceFill(array_merge($attributes, [
            'seller_id' => $seller->id,
            'code' => $code,
        ]));

        if ($promoCode->trashed()) {
            $promoCode->restore();
        }

        $promoCode->save();

        return $promoCode;
    }
}
