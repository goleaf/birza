<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        $response->assertStatus(200);
    }
}

