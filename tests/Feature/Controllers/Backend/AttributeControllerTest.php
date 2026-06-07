<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Attributes\Form as AttributeForm;
use App\Livewire\Backend\Attributes\Index as AttributeIndex;
use App\Models\Attribute;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_index_requires_authentication(): void
    {
        $response = $this->get(route('admin.attributes.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_attribute_index_displays_attributes(): void
    {
        $admin = Admin::factory()->create();
        Attribute::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attributes.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(AttributeIndex::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_attributes'))
            ->assertSee(__('common_actions'))
            ->assertSee(__('backend_attributes_actions_add_value'))
            ->assertSee(__('common_edit'))
            ->assertSee(__('common_delete'));
    }

    public function test_attribute_create_form_displays(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attributes.create'));

        $response->assertStatus(200)
            ->assertSeeLivewire(AttributeForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_attributes'))
            ->assertSee(__('backend_attributes_types_select'))
            ->assertSee(__('backend_attributes_fields_is_filterable'))
            ->assertSee(__('backend_attributes_fields_is_required'))
            ->assertSee(__('backend_attributes_fields_is_active'));
    }

    public function test_attribute_edit_form_displays(): void
    {
        $admin = Admin::factory()->create();
        $attribute = Attribute::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attributes.edit', $attribute));

        $response->assertStatus(200)
            ->assertSeeLivewire(AttributeForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_attributes'))
            ->assertSee(__('backend_attributes_types_boolean'))
            ->assertSee(__('backend_attributes_fields_is_active'));
    }
}
