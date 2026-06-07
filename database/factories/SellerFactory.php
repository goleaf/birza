<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SellerFactory extends Factory
{
    protected $model = Seller::class;

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
            'veterinary_certificate_number' => $this->faker->numerify('VET-####'),
            'bank_account' => $this->faker->iban(),
            'remember_token' => Str::random(10),
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

    public function withCategories(int $count = 3): static
    {
        return $this->afterCreating(function (Seller $seller) use ($count): void {
            $categories = Category::factory()
                ->count($count)
                ->create();

            $seller->categories()->syncWithoutDetaching($categories->modelKeys());
        });
    }

    public function withProducts(int $count = 3): static
    {
        return $this->afterCreating(function (Seller $seller) use ($count): void {
            Product::factory()
                ->count($count)
                ->for($seller, 'seller')
                ->create();
        });
    }

    public function withoutProducts(): static
    {
        return $this;
    }

    public function softDeleted(): static
    {
        return $this->afterCreating(function (Seller $seller): void {
            $seller->delete();
        });
    }
}
