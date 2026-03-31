<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AdminLandingController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return Auth::guard('admin')->check()
            ? redirect()->route('backend.dashboard')
            : redirect()->route('backend.login');
    }
}
