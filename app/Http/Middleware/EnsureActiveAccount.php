<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $user = Auth::guard($guard)->user();

        $isActive = $user && method_exists($user, 'getAttribute')
            ? $user->getAttribute('is_active')
            : ($user->is_active ?? null);

        if ($user && $isActive === false) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $redirectRoute = $guard === 'admin' ? 'admin.login' : 'home';

            return redirect()
                ->route($redirectRoute)
                ->with('error', __('messages_account_inactive'));
        }

        return $next($request);
    }
}
