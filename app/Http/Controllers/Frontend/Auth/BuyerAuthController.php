<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\Buyer;
use App\Traits\ThrottlesLogins;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class BuyerAuthController extends Controller
{
    use ThrottlesLogins;

    protected $maxAttempts = 5;
    protected $decayMinutes = 15;

    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'checkProfile']);
        $this->middleware('auth:buyer')->only(['verify', 'resend', 'checkProfile']);
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only(['verify', 'resend']);
    }

    protected function checkProfile()
    {
        $buyer = Auth::guard('buyer')->user();
        if (empty($buyer->company_name) || empty($buyer->company_code) || empty($buyer->address) || empty($buyer->phone)) {
            return redirect()->route('buyer.profile.edit')->with('warning', __('profile.complete_profile'));
        }
        return null;
    }

    public function showLoginForm()
    {
        return view('frontend.auth.buyer.login');
    }

    public function showRegistrationForm()
    {
        return view('frontend.auth.buyer.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users_buyers'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $buyer = Buyer::create([
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
        ]);

        // Temporarily disabled email verification
        // event(new Registered($buyer));

        // Automatically mark as verified for now
        $buyer->email_verified_at = now();
        $buyer->is_verified = true;
        $buyer->save();

        Auth::guard('buyer')->login($buyer);

        return $this->checkProfile() ?? redirect()->route('buyer.dashboard')->with('success', __('messages.registration_success'));
    }

    public function login(Request $request)
    {
        if (Auth::guard('seller')->check()) {
            throw ValidationException::withMessages([
                'login' => __('messages.messages_invalid_credentials')
            ]);
        }

        if (RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => __('messages.too_many_login_attempts', ['seconds' => $seconds])
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('buyer')->attempt($credentials, $request->filled('remember'))) {
            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();

            return $this->checkProfile() ?? redirect()->intended(route('buyer.dashboard'));
        }

        RateLimiter::hit($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('messages.messages_invalid_credentials'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('buyer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', __('messages.logout_success'));
    }

    public function verify(Request $request)
    {
        try {
            $user = Buyer::findOrFail($request->route('id'));

            if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
                throw new AuthenticationException(__('messages.verification_required'));
            }

            if ($user->hasVerifiedEmail()) {
                return redirect()->route('buyer.dashboard')->with('success', __('messages.email_already_verified'));
            }

            if ($user->markEmailAsVerified()) {
                $user->is_verified = true;
                $user->save();
                event(new Verified($user));
            }

            return $this->checkProfile() ?? redirect()->route('buyer.dashboard')->with('verified', true)->with('success', __('messages.verification_success'));

        } catch (\Exception $e) {
            return redirect()->route('buyer.dashboard')->with('error', __('messages.verification_required'));
        }
    }

    public function resend(Request $request)
    {
        $user = $request->user('buyer');

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('buyer.dashboard')->with('success', __('messages.email_already_verified'));
        }

        if (RateLimiter::tooManyAttempts('verify:'.$user->id, 3)) {
            return back()->with('error', __('messages.verification_check'));
        }

        $user->sendEmailVerificationNotification();
        RateLimiter::hit('verify:'.$user->id);

        return back()->with('resent', true)->with('success', __('messages.verification_sent'));
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }
}
