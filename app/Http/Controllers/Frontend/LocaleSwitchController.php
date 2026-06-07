<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\SwitchLocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleSwitchController extends Controller
{
    public function __invoke(SwitchLocaleRequest $request): RedirectResponse
    {
        $request->session()->put('locale', $request->resolvedLocale());

        return redirect()->back();
    }
}
