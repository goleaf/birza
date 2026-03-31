<?php

namespace Tests\Feature\Controllers\Frontend;

use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerLogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_logout(): void
    {
        $buyer = Buyer::factory()->create();

        $response = $this->actingAs($buyer, 'buyer')
            ->post(route('buyer.logout'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', __('messages_logout_success'));
        $this->assertGuest('buyer');
    }
}
