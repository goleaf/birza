<?php

namespace App\Http\Middleware;

use App\Enums\MarketplaceRole;
use App\Models\Users\Seller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $seller = Auth::guard(MarketplaceRole::Seller->guard())->user();

        abort_if(! $seller instanceof Seller, 403);

        Gate::forUser($seller)->authorize(MarketplaceRole::Seller->accessGate());

        return $next($request);
    }
}
