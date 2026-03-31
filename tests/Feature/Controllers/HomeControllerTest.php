<?php

namespace Tests\Feature\Controllers;

use App\Models\Category;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewHas('locales')
            ->assertViewHas('featuredCategories', fn (array $featuredCategories): bool => count($featuredCategories) === 4)
            ->assertViewHas('communityStats');
    }

    public function test_home_page_displays_database_backed_community_stats(): void
    {
        Seller::factory()->active()->count(17)->create();
        Seller::factory()->inactive()->count(2)->create();
        Buyer::factory()->active()->count(29)->create();
        Buyer::factory()->inactive()->count(3)->create();
        Category::factory()->count(43)->create();

        $response = $this->get('/');

        $response->assertOk()
            ->assertViewHas('communityStats', function (array $communityStats): bool {
                return $communityStats['sellers']['value'] === '17'
                    && $communityStats['buyers']['value'] === '29'
                    && $communityStats['categories']['value'] === '43';
            });
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
