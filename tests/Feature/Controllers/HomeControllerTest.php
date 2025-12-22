<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\Users\Seller;
use App\Models\Users\Buyer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('frontend.welcome');
    }

    public function test_home_redirects_active_seller_to_dashboard(): void
    {
        $seller = Seller::factory()->active()->create();
        
        $response = $this->actingAs($seller, 'seller')->get('/');

        $response->assertRedirect(route('seller.dashboard'));
    }

    public function test_home_logs_out_inactive_seller(): void
    {
        $seller = Seller::factory()->inactive()->create();
        
        $response = $this->actingAs($seller, 'seller')->get('/');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
        $this->assertGuest('seller');
    }

    public function test_home_redirects_active_buyer_to_dashboard(): void
    {
        $buyer = Buyer::factory()->active()->create();
        
        $response = $this->actingAs($buyer, 'buyer')->get('/');

        $response->assertRedirect(route('buyer.dashboard'));
    }

    public function test_home_logs_out_inactive_buyer(): void
    {
        $buyer = Buyer::factory()->inactive()->create();
        
        $response = $this->actingAs($buyer, 'buyer')->get('/');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
        $this->assertGuest('buyer');
    }
}

