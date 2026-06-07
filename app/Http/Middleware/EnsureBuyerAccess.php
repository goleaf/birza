<?php

namespace App\Http\Middleware;

use App\Enums\MarketplaceRole;
use App\Models\Users\Buyer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $buyer = Auth::guard(MarketplaceRole::Buyer->guard())->user();

        abort_if(! $buyer instanceof Buyer, 403);

        Gate::forUser($buyer)->authorize(MarketplaceRole::Buyer->accessGate());

        return $next($request);
    }
}
