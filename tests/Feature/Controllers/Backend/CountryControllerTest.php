<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Countries\Form as CountryForm;
use App\Livewire\Backend\Countries\Index as CountryIndex;
use App\Models\Country;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.countries.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_country_index_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        Country::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.countries.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(CountryIndex::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_countries'))
            ->assertSee(__('common_actions'))
            ->assertSee(__('common_edit'))
            ->assertSee(__('common_delete'));
    }

    public function test_country_create_form_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.countries.create'));

        $response->assertStatus(200)
            ->assertSeeLivewire(CountryForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_countries'))
            ->assertSee(__('backend_countries_regions_europe'))
            ->assertSee(__('backend_countries_fields_is_active'));
    }

    public function test_country_edit_form_displays_translation_tabs_and_localized_region_labels(): void
    {
        $admin = Admin::factory()->create();
        $country = Country::factory()->create([
            'region' => 'Europe',
            'country_name' => [
                'en' => 'Lithuania',
                'lt' => 'Lietuva',
            ],
        ]);

        $response = $this->withSession(['locale' => 'en'])
            ->actingAs($admin, 'admin')
            ->get(route('backend.countries.edit', $country));

        $response->assertStatus(200)
            ->assertSeeLivewire(CountryForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_countries'))
            ->assertSee(__('backend_countries_regions_europe'))
            ->assertSee(__('backend_countries_regions_asia'))
            ->assertSee(__('backend_countries_fields_is_active'))
            ->assertDontSee('backend_countries_regions_europe');

        foreach (config('app.locales') as $locale) {
            $response->assertSee('data-name="country-name-'.$locale.'"', false);
        }
    }
}
