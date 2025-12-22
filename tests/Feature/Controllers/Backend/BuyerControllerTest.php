<?php

namespace Tests\Feature\Controllers\Backend;

use Tests\TestCase;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_index_requires_authentication(): void
    {
        $response = $this->get(route('backend.buyers.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_buyer_index_displays_buyers(): void
    {
        $admin = Admin::factory()->create();
        Buyer::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('backend.buyers.index'));

        $response->assertStatus(200);
    }
}

