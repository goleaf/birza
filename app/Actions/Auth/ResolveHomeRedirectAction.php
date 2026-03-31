<?php

namespace App\Actions\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResolveHomeRedirectAction
{
    /**
     * @var array<string, string>
     */
    private const DASHBOARD_ROUTES = [
        'seller' => 'seller.dashboard',
        'buyer' => 'buyer.dashboard',
    ];

    public function __construct(
        private readonly LogoutGuardAction $logoutGuardAction,
    ) {}

    public function handle(Request $request): ?RedirectResponse
    {
        foreach (self::DASHBOARD_ROUTES as $guard => $dashboardRoute) {
            if (! Auth::guard($guard)->check()) {
                continue;
            }

            $user = Auth::guard($guard)->user();

            if ($user?->is_active) {
                return redirect()->route($dashboardRoute);
            }

            $this->logoutGuardAction->handle($request, $guard);

            return redirect()
                ->route('home')
                ->with('error', __('messages_account_deactivated'));
        }

        return null;
    }
}
