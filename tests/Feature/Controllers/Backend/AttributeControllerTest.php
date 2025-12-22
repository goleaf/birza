<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttributeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.attributes.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_attribute_index_displays_attributes(): void
    {
        $admin = Admin::factory()->create();
        Attribute::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.attributes.index'));

        $response->assertStatus(200);
    }
}

