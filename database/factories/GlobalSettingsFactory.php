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
            'admin_primary_color' => '#13261F',
            'admin_accent_color' => '#D2FF72',
            'admin_surface_color' => '#F4C16D',
            'admin_spotlight_tags' => ['ops', 'catalog', 'support'],
        ];
    }
}
