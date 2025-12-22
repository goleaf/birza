<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'alpha2' => $this->faker->unique()->countryCode(),
            'region' => $this->faker->randomElement(Country::getRegionOptions()),
            'is_active' => true,
            'country_name' => [
                'en' => $this->faker->country(),
                'lt' => $this->faker->country(),
            ],
            'description' => [
                'en' => $this->faker->paragraph(),
                'lt' => $this->faker->paragraph(),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}

