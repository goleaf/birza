<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_has_many_products(): void
    {
        $country = Country::factory()->create();
        Product::factory()->count(3)->create(['country_of_origin' => $country->id]);

        $this->assertCount(3, $country->products);
    }

    public function test_country_active_scope(): void
    {
        Country::factory()->active()->create();
        Country::factory()->create(['is_active' => false]);

        $activeCountries = Country::active()->get();

        $this->assertCount(1, $activeCountries);
        $activeCountries->each(function ($country) {
            $this->assertTrue($country->is_active);
        });
    }

    public function test_country_translatable_fields(): void
    {
        $country = Country::factory()->create([
            'country_name' => [
                'en' => 'Lithuania',
                'lt' => 'Lietuva',
            ],
        ]);

        $this->assertEquals('Lithuania', $country->getTranslation('country_name', 'en'));
        $this->assertEquals('Lietuva', $country->getTranslation('country_name', 'lt'));
    }

    public function test_country_get_region_options(): void
    {
        $regions = Country::getRegionOptions();
        $regionValues = array_column($regions, 'value');

        $this->assertIsArray($regions);
        $this->assertContains('Europe', $regionValues);
        $this->assertContains('Asia', $regionValues);
    }
}
