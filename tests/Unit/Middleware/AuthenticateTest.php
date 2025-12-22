<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateTest extends TestCase
{
    public function test_redirect_to_home_when_not_authenticated(): void
    {
        $middleware = new Authenticate();
        $request = Request::create('/protected');

        $redirectTo = $middleware->redirectTo($request);

        $this->assertEquals(route('home'), $redirectTo);
    }

    public function test_returns_null_for_json_requests(): void
    {
        $middleware = new Authenticate();
        $request = Request::create('/protected', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $redirectTo = $middleware->redirectTo($request);

        $this->assertNull($redirectTo);
    }
}

