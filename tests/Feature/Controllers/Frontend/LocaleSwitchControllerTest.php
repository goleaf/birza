<?php

namespace Tests\Feature\Controllers\Frontend;

use Tests\TestCase;

class LocaleSwitchControllerTest extends TestCase
{
    public function test_locale_switch_stores_selected_locale_in_session(): void
    {
        $response = $this->from(route('home'))
            ->get(route('language.switch', ['locale' => 'lt']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('locale', 'lt');
    }

    public function test_locale_switch_replaces_unknown_locale_with_fallback(): void
    {
        $this->withSession(['locale' => 'lt']);

        $response = $this->from(route('home', ['from' => 'language-switch']))
            ->get(route('language.switch', ['locale' => 'xx']));

        $response->assertRedirect(route('home', ['from' => 'language-switch']));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('locale', config('app.fallback_locale'));
    }
}
