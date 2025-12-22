<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.sellers.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_seller_index_displays_sellers(): void
    {
        $admin = Admin::factory()->create();
        Seller::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.sellers.index'));

        $response->assertStatus(200);
    }
}

