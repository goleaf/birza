<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        $response->assertStatus(200);
    }

    public function test_category_create_form_displays(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.categories.create'));

        $response->assertStatus(200);
    }
}

