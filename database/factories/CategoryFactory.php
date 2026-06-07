<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'parent_category_id' => null,
            'category_name' => [
                'en' => $this->faker->words(2, true),
                'lt' => $this->faker->words(2, true),
            ],
            'order' => $this->faker->numberBetween(1, 100),
            'slug' => [
                'en' => $this->faker->slug(),
                'lt' => $this->faker->slug(),
            ],
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

    public function topLevel(): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_category_id' => null,
        ]);
    }

    public function subcategory(?Category $parent = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_category_id' => $parent?->id ?? Category::factory(),
        ]);
    }

    public function empty(): static
    {
        return $this;
    }
}
