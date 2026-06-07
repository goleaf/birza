<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->word(),
                'lt' => $this->faker->word(),
            ],
            'type' => $this->faker->randomElement(array_keys(Attribute::TYPES)),
            'is_filterable' => $this->faker->boolean(),
            'is_required' => $this->faker->boolean(),
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
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function filterable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_filterable' => true,
        ]);
    }

    public function notFilterable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_filterable' => false,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => true,
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => false,
        ]);
    }
}
