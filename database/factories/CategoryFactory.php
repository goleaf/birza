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
}

