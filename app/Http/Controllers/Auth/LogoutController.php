<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LogoutGuardAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private readonly LogoutGuardAction $logoutGuardAction,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $guard = (string) $request->route('guard');
        $redirectRoute = (string) $request->route('redirectRoute', 'home');
        $flashMessage = $request->route('flashMessage');

        $this->logoutGuardAction->handle($request, $guard);

        $response = redirect()->route($redirectRoute);

        if (is_string($flashMessage) && $flashMessage !== '') {
            return $response->with('success', __($flashMessage));
        }

        return $response;
    }
}
