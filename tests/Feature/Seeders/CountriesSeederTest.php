<?php

namespace Tests\Feature\Seeders;

use App\Models\Country;
use Database\Seeders\test_information\CountriesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountriesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_countries_seeder_loads_translated_names_and_is_idempotent(): void
    {
        $this->seed(CountriesSeeder::class);

        $lithuania = Country::query()->where('alpha2', 'lt')->firstOrFail();
        $initialCountryCount = Country::query()->count();

        $this->assertSame('Lithuania', $lithuania->getTranslation('country_name', 'en'));
        $this->assertSame('Lietuva', $lithuania->getTranslation('country_name', 'lt'));

        $this->seed(CountriesSeeder::class);

        $this->assertSame($initialCountryCount, Country::query()->count());
        $this->assertSame(
            'Lithuania',
            Country::query()->where('alpha2', 'lt')->firstOrFail()->getTranslation('country_name', 'en')
        );
    }
}
