<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\Seller;
use App\Traits\ThrottlesLogins;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SellerAuthController extends Controller
{
    use ThrottlesLogins;

    protected $maxAttempts = 5;
    protected $decayMinutes = 15;

    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'checkProfile', 'verify', 'resend']);
        $this->middleware('auth:seller')->only(['checkProfile']);
        $this->middleware('throttle:6,1')->only(['verify', 'resend']);
        $this->middleware('verified:seller')->only(['dashboard']);
    }

    protected function checkProfile()
    {
        $seller = Auth::guard('seller')->user();
        if (empty($seller->company_name) || empty($seller->company_code) || empty($seller->address) || empty($seller->phone)) {
            return redirect()->route('seller.profile.edit')->with('warning', __('profile.complete_profile'));
        }
        return null;
    }

    public function showLoginForm()
    {
        return view('frontend.auth.seller.login');
    }

    public function showRegistrationForm()
    {
        return view('frontend.auth.seller.register');
    }
//////////////////////////////// netu
    public function showLinkRequestForm()
    {
        return view('frontend.auth.seller.passwords.email');
    }

    ///////// netu
    public function showResetForm($token = null)
    {
        if (!$token) {
            return redirect()->route('seller.password.request')->with('error', __('passwords.token'));
        }

        $seller = Seller::where('remember_token', $token)->first();
        if (!$seller || 
            !$seller->password_reset_at || 
            now()->diffInMinutes($seller->password_reset_at) > 60) {
            return redirect()->route('seller.password.request')->with('error', __('passwords.token'));
        }

        return view('frontend.auth.seller.passwords.reset')->with([
            'token' => $token,
            'email' => $seller->email,
            'emailMessage' => __('passwords.reset_for_email', ['email' => $seller->email])
        ]);
    }

    ///// netu
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $seller = Seller::where('email', $request->email)->first();

        if (!$seller) {
            throw ValidationException::withMessages([
                'email' => __('passwords.user')
            ]);
        }

        $token = sha1(Str::random(40));
        $seller->remember_token = $token;
        $seller->password_reset_at = now();
        $seller->save();

        $resetUrl = route('seller.password.reset', ['hash' => $token]);
        $message = __('emails.reset_password_body') . "\n\n" . $resetUrl;

        Mail::raw($message, function($mail) use ($seller) {
            $mail->to($seller->email)
                ->subject(__('emails.reset_password_subject'));
        });

        return back()->with('status', __('auth.email_password_reset_sent'));
    }

    public function register(Request $request)
    {
        // Check if email already exists
        $existingUser = Seller::where('email', strtolower($request->email))->first();
        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => __('auth.email_already_exists')
            ]);
        }

        $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $verificationToken = sha1(Str::random(40));

        $seller = Seller::create([
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'remember_token' => $verificationToken
        ]);

        // Send verification email
        $verificationUrl = route('seller.verification.verify', [
            'hash' => $verificationToken,
        ]);
        
        $verificationMessage = __('emails.verify_email_body') . "\n\n" . $verificationUrl;

        Mail::raw($verificationMessage, function($mail) use ($seller) {
            $mail->to($seller->email)->subject(__('emails.verify_email_subject'));
        });

        // Store registration success in session
        session()->flash('registration_success', true);
        session()->flash('registered_email', $seller->email);

        return redirect()->route('seller.register.success');
    }

    public function showRegistrationSuccess()
    {
        if (!session('registration_success')) {
            return redirect()->route('seller.register');
        }

        return view('frontend.auth.seller.register-success', [
            'email' => session('registered_email')
        ]);
    }

    public function login(Request $request)
    {
        if (Auth::guard('buyer')->check()) {
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

        if (Auth::guard('seller')->attempt($credentials, $request->filled('remember'))) {
            $seller = Auth::guard('seller')->user();
            
            if (!$seller->is_verified) {
                Auth::guard('seller')->logout();
                throw ValidationException::withMessages([
                    'email' => __('auth.verification_required')
                ]);
            }

            if (!$seller->is_active) {
                Auth::guard('seller')->logout();
                throw ValidationException::withMessages([
                    'email' => __('messages.account_inactive')
                ]);
            }

            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();

            return $this->checkProfile() ?? redirect()->intended(route('seller.dashboard'));
        }

        RateLimiter::hit($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('messages.messages_invalid_credentials'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('seller')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', __('messages.logout_success'));
    }

    public function verify(Request $request)
    {

        $user = Seller::where('remember_token', $request->route('hash'))->firstOrFail();

        if (!hash_equals((string) $request->route('hash'), $user->remember_token)) {
            throw new AuthenticationException(__('messages.verification_required'));
        }

        if ($user->is_verified) {
            return redirect()->route('seller.login')
                ->with('success', __('messages.email_already_verified'));
        }

        $user->is_active = true;
        $user->is_verified = true;
        $user->remember_token = null;
        $user->save();

        return redirect()->route('seller.login')->with('success', __('messages.verification_success'));
    }

    public function resend(Request $request)
    {
        $user = $request->user('seller');

        if ($user->is_verified) {
            return redirect()->route('seller.dashboard')
                ->with('success', __('messages.email_already_verified'));
        }

        if (RateLimiter::tooManyAttempts('verify:'.$user->id, 3)) {
            return back()->with('error', __('messages.verification_check'));
        }

        $user->remember_token = sha1(Str::random(40));
        $user->save();

        $verificationUrl = route('seller.verification.verify', [
            'hash' => $user->remember_token
        ]);
        
        $message = __('emails.verify_email_body') . "\n\n" . $verificationUrl;

        Mail::raw($message, function($mail) use ($user) {
            $mail->to($user->email)
                ->subject(__('emails.verify_email_subject'));
        });

        RateLimiter::hit('verify:'.$user->id);

        return back()->with('resent', true)
            ->with('success', __('messages.verification_sent'));
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }

    public function showVerificationNotice()
    {
        $user = Auth::guard('seller')->user();
        
        if (!$user) {
            return redirect()->route('seller.login');
        }

        return $user->is_verified
            ? redirect()->route('seller.dashboard')
            : view('frontend.auth.seller.verify');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $seller = Seller::where('email', $request->email)->first();
        if (!$seller || 
            !hash_equals($request->token, $seller->remember_token) || 
            !$seller->password_reset_at || 
            now()->diffInMinutes($seller->password_reset_at) > 60) {
            throw ValidationException::withMessages([
                'email' => __('passwords.token')
            ]);
        }

        $seller->password = Hash::make($request->password);
        $seller->password_reset_at = null;
        $seller->remember_token = null;
        $seller->save();

        return redirect()->route('seller.login')
            ->with('success', __('passwords.reset'));
    }

}