<?php

namespace Database\Factories;

use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'company_name' => $this->faker->company(),
            'company_code' => $this->faker->numerify('#########'),
            'vat_code' => $this->faker->numerify('LT#########'),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'veterinary_certificate_number' => $this->faker->numerify('VET-####'),
            'is_verified' => true,
            'is_active' => true,
            'balance' => $this->faker->randomFloat(2, 0, 10000),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

