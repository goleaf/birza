<?php

namespace Tests\Feature\Controllers\Frontend;

use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerLogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_logout(): void
    {
        $seller = Seller::factory()->create();

        $response = $this->actingAs($seller, 'seller')
            ->post(route('seller.logout'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', __('messages_logout_success'));
        $this->assertGuest('seller');
    }
}
