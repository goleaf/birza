<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Attributes\Values\Form as AttributeValueForm;
use App\Livewire\Backend\Attributes\Values\Index as AttributeValueIndex;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeValueControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_value_index_requires_authentication(): void
    {
        $attribute = Attribute::factory()->create();

        $response = $this->get(route('admin.attributes.values.index', $attribute));

        $response->assertRedirect(route('home'));
    }

    public function test_attribute_value_index_displays_values_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $attribute = Attribute::factory()->create();
        AttributeValue::factory()->count(3)->create([
            'attribute_id' => $attribute->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attributes.values.index', $attribute));

        $response->assertStatus(200)
            ->assertSeeLivewire(AttributeValueIndex::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_attributes'))
            ->assertSee(__('common_values'))
            ->assertSee(__('common_actions'))
            ->assertSee(__('common_edit'))
            ->assertSee(__('common_delete'));
    }

    public function test_attribute_value_create_form_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $attribute = Attribute::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attributes.values.create', $attribute));

        $response->assertStatus(200)
            ->assertSeeLivewire(AttributeValueForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_attributes'))
            ->assertSee(__('common_values'))
            ->assertSee(__('backend_attribute_values_fields_is_active'));
    }

    public function test_attribute_value_edit_form_displays_for_admin(): void
    {
        $admin = Admin::factory()->create();
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attributes.values.edit', [
                'attribute' => $attribute,
                'value' => $attributeValue,
            ]));

        $response->assertStatus(200)
            ->assertSeeLivewire(AttributeValueForm::class)
            ->assertSee(__('backend_dashboard_title'))
            ->assertSee(__('navigation_attributes'))
            ->assertSee(__('common_values'))
            ->assertSee(__('backend_attribute_values_fields_is_active'));
    }
}
