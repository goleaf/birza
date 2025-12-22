<?php

namespace Database\Factories;

use App\Models\GlobalSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

class GlobalSettingsFactory extends Factory
{
    protected $model = GlobalSettings::class;

    public function definition(): array
    {
        return [
            'portal_additional_price' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}

