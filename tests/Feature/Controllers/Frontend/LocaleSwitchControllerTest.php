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

    public function test_locale_switch_rejects_unknown_locale(): void
    {
        $this->withSession(['locale' => 'en']);

        $response = $this->from(route('home'))
            ->get(route('language.switch', ['locale' => 'xx']));

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('locale');
        $response->assertSessionHas('locale', 'en');
    }
}
