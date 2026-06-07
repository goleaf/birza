<?php

namespace App\Http\Middleware;

use App\Enums\MarketplaceRole;
use App\Models\Users\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard(MarketplaceRole::Admin->guard())->user();

        abort_if(! $admin instanceof Admin, 403);

        Gate::forUser($admin)->authorize(MarketplaceRole::Admin->accessGate());

        return $next($request);
    }
}
