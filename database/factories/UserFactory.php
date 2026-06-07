<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
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
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $profileAttributes
     */
    public function asBuyer(array $profileAttributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($profileAttributes): void {
            if (! Schema::hasTable('users_buyers') || ! Schema::hasColumn('users_buyers', 'user_id')) {
                return;
            }

            Buyer::factory()
                ->forUser($user)
                ->create($profileAttributes);
        });
    }

    /**
     * @param  array<string, mixed>  $profileAttributes
     */
    public function asSeller(array $profileAttributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($profileAttributes): void {
            if (! Schema::hasTable('users_sellers') || ! Schema::hasColumn('users_sellers', 'user_id')) {
                return;
            }

            Seller::factory()
                ->forUser($user)
                ->create($profileAttributes);
        });
    }

    /**
     * @param  array<string, mixed>  $buyerAttributes
     * @param  array<string, mixed>  $sellerAttributes
     */
    public function asBuyerAndSeller(array $buyerAttributes = [], array $sellerAttributes = []): static
    {
        return $this
            ->asBuyer($buyerAttributes)
            ->asSeller($sellerAttributes);
    }
}
