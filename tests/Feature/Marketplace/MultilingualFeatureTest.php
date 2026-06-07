<?php

namespace Tests\Feature\Marketplace;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Support\MarketplaceTestHelpers;
use Tests\TestCase;

class MultilingualFeatureTest extends TestCase
{
    use MarketplaceTestHelpers;
    use RefreshDatabase;

    public function test_language_switcher_stores_supported_locale_and_invalid_locale_falls_back(): void
    {
        $this->from(route('home'))
            ->get(route('language.switch', ['locale' => 'lt']))
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'lt');

        $this->withSession(['locale' => 'lt'])
            ->from(route('home'))
            ->get(route('language.switch', ['locale' => 'xx']))
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', config('app.fallback_locale'));
    }

    public function test_public_pages_use_selected_guest_locale(): void
    {
        $this->withSession(['locale' => 'en'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee($this->translationFor('en', 'welcome_buyer_login_button'))
            ->assertDontSee('welcome_buyer_login_button');

        $this->withSession(['locale' => 'lt'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee($this->translationFor('lt', 'welcome_buyer_login_button'))
            ->assertDontSee('welcome_buyer_login_button');
    }

    public function test_authenticated_buyer_pages_use_selected_locale(): void
    {
        $buyer = $this->createBuyer();

        $this->actingAs($buyer, 'buyer')
            ->withSession(['locale' => 'en'])
            ->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee($this->translationFor('en', 'product_search_list'))
            ->assertDontSee('product_search_list');
    }

    public function test_order_status_labels_are_translated_and_do_not_leak_keys(): void
    {
        $buyer = $this->createBuyer();
        $order = $this->createOrderWithItem($buyer, orderAttributes: [
            'status' => OrderStatus::Pending,
        ]);

        $this->actingAs($buyer, 'buyer')
            ->withSession(['locale' => 'en'])
            ->get(route('buyer.orders.show', $order))
            ->assertOk()
            ->assertSee($this->translationFor('en', 'orders.status.pending'))
            ->assertSee($this->translationFor('en', 'orders.status.pending.description'))
            ->assertDontSee('orders.status.pending');
    }

    public function test_core_marketplace_translation_keys_exist_for_all_supported_languages(): void
    {
        $requiredKeys = [
            'cart_messages_order_placed',
            'cart_messages_shipping_address_required',
            'cart_messages_product_unavailable',
            'orders.status.pending',
            'orders.status.accepted',
            'orders.status.messages.transition_not_allowed',
            'notifications.orders.status_changed.subject',
            'validation.attributes.email',
            'validation.attributes.locale',
            'welcome_buyer_login_button',
            'welcome_seller_login_button',
        ];

        foreach ((array) config('app.locales') as $locale) {
            $lines = $this->translationsFor($locale);

            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $lines, "Missing [{$key}] in [{$locale}].");
                $this->assertNotSame($key, $lines[$key], "Translation [{$key}] in [{$locale}] must not leak its key.");
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function translationsFor(string $locale): array
    {
        $path = lang_path("{$locale}.json");

        $this->assertFileExists($path);

        return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    private function translationFor(string $locale, string $key): string
    {
        $lines = $this->translationsFor($locale);

        $this->assertArrayHasKey($key, $lines);

        return (string) $lines[$key];
    }
}
