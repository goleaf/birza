<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    public function test_set_locale_from_session(): void
    {
        $middleware = new SetLocale;
        $request = Request::create('/');
        Session::put('locale', 'lt');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('lt', app()->getLocale());
    }

    public function test_set_locale_default_when_no_session(): void
    {
        $middleware = new SetLocale;
        $request = Request::create('/');
        Session::forget('locale');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(config('app.locale'), app()->getLocale());
    }

    public function test_set_locale_defaults_when_session_locale_is_invalid(): void
    {
        $middleware = new SetLocale;
        $request = Request::create('/');
        Session::put('locale', 'invalid');

        $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(config('app.locale'), app()->getLocale());
    }
}
