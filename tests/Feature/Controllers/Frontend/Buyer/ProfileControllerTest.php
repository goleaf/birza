<?php

namespace Tests\Feature\Controllers\Frontend\Buyer;

use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_requires_authentication(): void
    {
        $response = $this->get(route('buyer.profile.edit'));

        $response->assertRedirect(route('home'));
    }

    public function test_profile_edit_displays_for_authenticated_buyer(): void
    {
        $buyer = Buyer::factory()->create();

        $response = $this->actingAs($buyer, 'buyer')
            ->get(route('buyer.profile.edit'));

        $response->assertStatus(200)
            ->assertSee(__('common_dashboard'))
            ->assertSee(__('profile'))
            ->assertSee(__('profile_edit_profile'))
            ->assertSee(__('profile_update_password'))
            ->assertSee('data-name="profile-tab"', false)
            ->assertSee('data-name="password-tab"', false)
            ->assertSee('role="tabpanel"', false);
    }
}
