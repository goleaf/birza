<?php

namespace Tests\Feature\Controllers\Backend;

use App\Livewire\Backend\Categories\Form as CategoryForm;
use App\Livewire\Backend\Categories\Index as CategoryIndex;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Users\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.categories.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_category_index_displays_categories(): void
    {
        $admin = Admin::factory()->create();
        Category::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.categories.index'));

        $response->assertStatus(200)
            ->assertSeeLivewire(CategoryIndex::class)
            ->assertSee(__('common_filter'))
            ->assertSee(__('common_actions'))
            ->assertSee(__('backend_categories_filters_structure'))
            ->assertSee(__('backend_categories_filters_with_attributes'))
            ->assertSee(__('common_edit'))
            ->assertSee(__('common_delete'));
    }

    public function test_category_index_filters_by_structure_and_attribute_presence(): void
    {
        $parentCategory = Category::factory()->create([
            'category_name' => ['en' => 'Root With Attributes', 'lt' => 'Pagrindinė su atributais'],
        ]);
        $rootWithoutAttributes = Category::factory()->create([
            'category_name' => ['en' => 'Root Without Attributes', 'lt' => 'Pagrindinė be atributų'],
        ]);
        $childCategory = Category::factory()->create([
            'parent_category_id' => $parentCategory->id,
            'category_name' => ['en' => 'Child Category', 'lt' => 'Subkategorija'],
        ]);
        $childWithoutAttributes = Category::factory()->create([
            'parent_category_id' => $parentCategory->id,
            'category_name' => ['en' => 'Child Without Attributes', 'lt' => 'Subkategorija be atributų'],
        ]);

        $attribute = Attribute::factory()->create();

        $parentCategory->attributes()->attach($attribute);
        $childCategory->attributes()->attach($attribute);

        Livewire::test(CategoryIndex::class)
            ->set('structureFilter', 'child')
            ->set('attributePresenceFilter', 'with')
            ->assertSee($childCategory->getTranslation('category_name', app()->getLocale()))
            ->assertDontSee($childWithoutAttributes->getTranslation('category_name', app()->getLocale()))
            ->assertDontSee($rootWithoutAttributes->getTranslation('category_name', app()->getLocale()));
    }

    public function test_category_create_form_displays(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.categories.create'));

        $response->assertStatus(200)
            ->assertSeeLivewire(CategoryForm::class);
    }

    public function test_category_edit_form_displays(): void
    {
        $admin = Admin::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.categories.edit', $category));

        $response->assertStatus(200)
            ->assertSeeLivewire(CategoryForm::class);
    }
}
