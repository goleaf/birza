<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Models\Users\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BuyerFactory extends Factory
{
    protected $model = Buyer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'company_name' => $this->faker->company(),
            'company_code' => $this->faker->numerify('#########'),
            'vat_code' => $this->faker->numerify('LT#########'),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'bank_account' => $this->faker->iban(),
            'credit_balance' => $this->faker->randomFloat(2, 0, 10000),
            'remember_token' => Str::random(10),
            'is_verified' => true,
            'is_active' => true,
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

    public function blocked(): static
    {
        return $this->inactive();
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
            'is_verified' => false,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'email_verified_at' => $user->email_verified_at,
            'is_active' => $user->is_active,
            'is_verified' => $user->email_verified_at !== null,
        ]);
    }

    public function withUser(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => User::factory(),
        ]);
    }

    public function withOrders(int $count = 3): static
    {
        return $this->afterCreating(function (Buyer $buyer) use ($count): void {
            Order::factory()
                ->count($count)
                ->for($buyer, 'buyer')
                ->withItems()
                ->create();
        });
    }

    public function withEmptyCart(): static
    {
        return $this->afterCreating(function (Buyer $buyer): void {
            Cart::factory()
                ->for($buyer, 'buyer')
                ->create();
        });
    }

    public function softDeleted(): static
    {
        return $this->afterCreating(function (Buyer $buyer): void {
            $buyer->delete();
        });
    }
}
