<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromoCodeRedemption>
 */
class PromoCodeRedemptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promo_code_id' => PromoCode::factory(),
            'user_id' => Buyer::factory(),
            'order_id' => Order::factory(),
            'discount_amount' => $this->faker->randomFloat(2, 1, 50),
        ];
    }
}
