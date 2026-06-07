<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => $this->faker->randomElement(['billing', 'shipping', 'office']),
            'name' => $this->faker->name(),
            'line_one' => $this->faker->streetAddress(),
            'line_two' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => $this->faker->countryCode(),
            'phone' => $this->faker->phoneNumber(),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }

    public function billing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'label' => 'billing',
        ]);
    }

    public function shipping(): static
    {
        return $this->state(fn (array $attributes): array => [
            'label' => 'shipping',
        ]);
    }

    public function lithuanian(): static
    {
        return $this->state(fn (array $attributes): array => [
            'city' => $this->faker->randomElement(['Vilnius', 'Kaunas', 'Klaipeda', 'Siauliai']),
            'postal_code' => 'LT-'.$this->faker->numberBetween(10000, 99999),
            'country_code' => 'LT',
            'phone' => '+3706'.$this->faker->numerify('#######'),
        ]);
    }
}
