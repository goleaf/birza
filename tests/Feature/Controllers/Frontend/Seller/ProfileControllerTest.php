<?php

namespace Tests\Feature\Controllers\Frontend\Seller;

use Tests\TestCase;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_requires_authentication(): void
    {
        $response = $this->get(route('seller.profile.edit'));

        $response->assertRedirect(route('home'));
    }

    public function test_profile_edit_displays_for_authenticated_seller(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAs($seller, 'seller')
            ->get(route('seller.profile.edit'));

        $response->assertStatus(200);
    }
}

