<?php

namespace Tests\Feature\Support;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Country;
use App\Models\GlobalSettings;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Admin;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpotlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_spotlight_returns_empty_results_for_guests(): void
    {
        $response = $this->getJson(route('mary.spotlight', ['search' => 'admin']));

        $response->assertOk()
            ->assertExactJson([]);
    }

    public function test_spotlight_returns_quick_actions_and_entity_matches_for_admins(): void
    {
        $admin = Admin::factory()->create();
        $country = Country::factory()->create([
            'alpha2' => 'ND',
            'country_name' => [
                'en' => 'Nordic Country',
                'lt' => 'Nordic Country',
            ],
        ]);
        $category = Category::factory()->create([
            'category_name' => [
                'en' => 'Nordic Category',
                'lt' => 'Nordic Category',
            ],
        ]);
        $buyer = Buyer::factory()->create([
            'name' => 'Nordic Buyer',
            'email' => 'nordic-buyer@example.test',
            'company_name' => 'Nordic Buyer Co',
        ]);
        $seller = Seller::factory()->create([
            'name' => 'Nordic Seller',
            'email' => 'nordic-seller@example.test',
            'company_name' => 'Nordic Seller Co',
        ]);
        $product = Product::factory()->create([
            'name' => 'Nordic Product',
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'country_of_origin' => $country->id,
        ]);
        $attribute = Attribute::factory()->create([
            'name' => [
                'en' => 'Nordic Attribute',
                'lt' => 'Nordic Attribute',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('mary.spotlight', ['search' => 'Nordic']));

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Nordic Product',
                'link' => route('backend.products.edit', $product, false),
            ])
            ->assertJsonFragment([
                'name' => 'Nordic Seller',
                'link' => route('backend.sellers.edit', $seller, false),
            ])
            ->assertJsonFragment([
                'name' => 'Nordic Buyer',
                'link' => route('backend.buyers.edit', $buyer, false),
            ])
            ->assertJsonFragment([
                'name' => 'Nordic Category',
                'link' => route('backend.categories.edit', $category, false),
            ])
            ->assertJsonFragment([
                'name' => 'Nordic Country',
                'link' => route('backend.countries.edit', $country, false),
            ])
            ->assertJsonFragment([
                'name' => 'Nordic Attribute',
                'link' => route('backend.attributes.edit', $attribute, false),
            ]);
    }

    public function test_spotlight_returns_quick_actions_and_orders(): void
    {
        $admin = Admin::factory()->create();
        $buyer = Buyer::factory()->create([
            'name' => 'Action Buyer',
            'email' => 'action-buyer@example.test',
        ]);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
        ]);

        $settingsSearch = (string) __('navigation_global_settings');

        $quickActionResponse = $this->actingAs($admin, 'admin')
            ->getJson(route('mary.spotlight', ['search' => $settingsSearch]));

        $quickActionResponse->assertOk()
            ->assertJsonFragment([
                'name' => __('navigation_global_settings'),
                'link' => route('backend.settings.index', absolute: false),
            ]);

        $orderResponse = $this->actingAs($admin, 'admin')
            ->getJson(route('mary.spotlight', ['search' => (string) $order->id]));

        $orderResponse->assertOk()
            ->assertJsonFragment([
                'name' => '#'.$order->id,
                'link' => route('backend.orders.show', $order, false),
            ]);
    }

    public function test_spotlight_matches_custom_admin_tags_from_settings(): void
    {
        $admin = Admin::factory()->create();
        GlobalSettings::factory()->create([
            'admin_spotlight_tags' => ['ops', 'catalog'],
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('mary.spotlight', ['search' => 'catalog']));

        $response->assertOk()
            ->assertJsonFragment([
                'name' => __('navigation_products'),
                'link' => route('backend.products.create', absolute: false),
            ]);
    }
}
