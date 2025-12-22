<?php

namespace Database\Factories;

use App\Models\SellerTransaction;
use App\Models\Users\Seller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerTransactionFactory extends Factory
{
    protected $model = SellerTransaction::class;

    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'order_id' => Order::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['sale', 'refund', 'commission']),
            'description' => $this->faker->sentence(),
        ];
    }
}

