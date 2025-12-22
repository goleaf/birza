<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index()
    {
        $guards = ['seller', 'buyer'];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if (Auth::guard($guard)->user()->is_active) {
                    return redirect()->route("$guard.dashboard");
                }

                Auth::guard($guard)->logout();
                Session::flush();
                return redirect()->route('home')->with('error', __('messages.account_deactivated'));
            }
        }

        return view('frontend.welcome');
    }
}
