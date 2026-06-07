<?php

namespace App\Actions\Auth;

use App\Enums\MarketplaceRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResolveHomeRedirectAction
{
    public function __construct(
        private readonly LogoutGuardAction $logoutGuardAction,
    ) {}

    public function handle(Request $request): ?RedirectResponse
    {
        foreach ([MarketplaceRole::Seller, MarketplaceRole::Buyer] as $role) {
            $guard = $role->guard();

            if (! Auth::guard($guard)->check()) {
                continue;
            }

            $user = Auth::guard($guard)->user();

            if ($user?->is_active) {
                return redirect()->route($role->dashboardRoute());
            }

            $this->logoutGuardAction->handle($request, $guard);

            return redirect()
                ->route('home')
                ->with('error', __('messages_account_deactivated'));
        }

        return null;
    }
}
