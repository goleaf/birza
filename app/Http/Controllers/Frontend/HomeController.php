<?php

namespace App\Http\Controllers\Frontend;

use App\Actions\Auth\ResolveHomeRedirectAction;
use App\Actions\Frontend\BuildWelcomePageDataAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(
        Request $request,
        ResolveHomeRedirectAction $resolveHomeRedirectAction,
        BuildWelcomePageDataAction $buildWelcomePageDataAction,
    ): View|RedirectResponse {
        $redirect = $resolveHomeRedirectAction->handle($request);

        if ($redirect !== null) {
            return $redirect;
        }

        return view('frontend.welcome', $buildWelcomePageDataAction->handle());
    }
}
