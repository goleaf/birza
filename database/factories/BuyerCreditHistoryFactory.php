<?php

namespace Database\Factories;

use App\Models\BuyerCreditHistory;
use App\Models\CreditAttachment;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
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
            'type' => $this->faker->randomElement(['add', 'deduct']),
            'balance_after' => $balanceAfter,
            'admin_id' => Admin::factory(),
            'note' => $this->faker->sentence(),
        ];
    }

    public function add(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'add',
        ]);
    }

    public function deduct(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'deduct',
        ]);
    }

    public function withAttachment(): static
    {
        return $this->afterCreating(function (BuyerCreditHistory $history): void {
            CreditAttachment::factory()
                ->for($history, 'creditHistory')
                ->create();
        });
    }
}
