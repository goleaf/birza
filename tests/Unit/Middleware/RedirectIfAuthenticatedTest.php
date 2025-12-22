<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Users\Seller;
use App\Models\Users\Buyer;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_seller_to_dashboard_when_authenticated(): void
    {
        $middleware = new RedirectIfAuthenticated();
        $seller = Seller::factory()->create();
        $request = Request::create('/login');

        Auth::guard('seller')->login($seller);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'seller');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('seller.dashboard'), $response->headers->get('Location'));
    }

    public function test_redirects_buyer_to_dashboard_when_authenticated(): void
    {
        $middleware = new RedirectIfAuthenticated();
        $buyer = Buyer::factory()->create();
        $request = Request::create('/login');

        Auth::guard('buyer')->login($buyer);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'buyer');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('buyer.dashboard'), $response->headers->get('Location'));
    }

    public function test_allows_access_when_not_authenticated(): void
    {
        $middleware = new RedirectIfAuthenticated();
        $request = Request::create('/login');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }
}

