<?php

namespace Database\Factories;

use App\Models\BuyerCreditHistory;
use App\Models\Users\Buyer;
use App\Models\Users\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuyerCreditHistoryFactory extends Factory
{
    protected $model = BuyerCreditHistory::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 10, 1000);
        $balanceAfter = $this->faker->randomFloat(2, 0, 10000);

        return [
            'buyer_id' => Buyer::factory(),
            'amount' => $amount,
            'type' => $this->faker->randomElement(['credit', 'debit']),
            'balance_after' => $balanceAfter,
            'admin_id' => Admin::factory(),
            'note' => $this->faker->sentence(),
        ];
    }
}

