<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use App\Traits\ThrottlesLogins;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    use ThrottlesLogins;

    protected $maxAttempts = 5;
    protected $decayMinutes = 15;
    protected $userType;
    protected $guard;
    protected $model;

    public function __construct(Request $request)
    {
        $this->userType = $request->segment(1); // 'buyer' or 'seller'
        $this->guard = $this->userType;
        $this->model = $this->userType === 'buyer' ? Buyer::class : Seller::class;

        $this->middleware('guest')->except(['logout', 'checkProfile', 'verify', 'resend']);
        $this->middleware("auth:{$this->guard}")->only(['checkProfile']);
        $this->middleware('throttle:6,1')->only(['verify', 'resend']);
        $this->middleware("verified:{$this->guard}")->only(['dashboard']);
    }

    protected function checkProfile()
    {
        $user = Auth::guard($this->guard)->user();
        if (empty($user->company_name) || empty($user->company_code) || empty($user->address) || empty($user->phone)) {
            return redirect()->route("{$this->userType}.profile.edit")
                ->with('warning', __('profile.complete_profile'));
        }
        return null;
    }

    public function showLoginForm()
    {
        return view('frontend.auth.login', [
            'userType' => $this->userType
        ]);
    }

    public function showRegistrationForm()
    {
        return view('frontend.auth.register', [
            'userType' => $this->userType
        ]);
    }

    public function register(Request $request)
    {
        $modelClass = $this->model;
        
        // Check if email already exists
        $existingUser = $modelClass::where('email', strtolower($request->email))->first();
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

        $userData = [
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'remember_token' => $verificationToken,
            'bank_account' => ''
        ];

        // Add veterinary certificate number for sellers only
        if ($this->userType === 'seller') {
            $userData['veterinary_certificate_number'] = '';
        }

        $user = $modelClass::create($userData);

        // Send verification email
        $verificationUrl = route("{$this->userType}.verification.verify", [
            'hash' => $verificationToken,
        ]);
        
        $verificationMessage = __('emails.verify_email_body') . "\n\n" . $verificationUrl;

        Mail::raw($verificationMessage, function($mail) use ($user) {
            $mail->to($user->email)->subject(__('emails.verify_email_subject'));
        });

        session()->flash('registration_success', true);
        session()->flash('registered_email', $user->email);

        return redirect()->route("{$this->userType}.register.success");
    }

    public function login(Request $request)
    {
        $otherGuard = $this->userType === 'buyer' ? 'seller' : 'buyer';
        
        if (Auth::guard($otherGuard)->check()) {
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

        if (Auth::guard($this->guard)->attempt($credentials, $request->filled('remember'))) {
            $user = Auth::guard($this->guard)->user();
            
            if (!$user->is_verified) {
                Auth::guard($this->guard)->logout();
                throw ValidationException::withMessages([
                    'email' => __('auth.verification_required')
                ]);
            }

            if (!$user->is_active) {
                Auth::guard($this->guard)->logout();
                throw ValidationException::withMessages([
                    'email' => __('messages.account_inactive')
                ]);
            }

            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();

            return $this->checkProfile() ?? redirect()->intended(route("{$this->userType}.dashboard"));
        }

        RateLimiter::hit($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('messages.messages_invalid_credentials'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard($this->guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', __('messages_logout_success'));
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }

    public function showLinkRequestForm()
    {
        return view('frontend.auth.passwords.email', [
            'userType' => $this->userType
        ]);
    }

    public function showResetForm($token = null)
    {
        if (!$token) {
            return redirect()->route("{$this->userType}.password.request")
                ->with('error', __('passwords.token'));
        }

        $user = $this->model::where('remember_token', $token)->first();
        if (!$user || 
            !$user->password_reset_at || 
            now()->diffInMinutes($user->password_reset_at) > 60) {
            return redirect()->route("{$this->userType}.password.request")
                ->with('error', __('passwords.token'));
        }

        return view('frontend.auth.passwords.reset', [
            'token' => $token,
            'email' => $user->email,
            'emailMessage' => __('passwords.reset_for_email', ['email' => $user->email]),
            'userType' => $this->userType
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $user = $this->model::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => __('passwords.user')
            ]);
        }

        $token = sha1(Str::random(40));
        $user->remember_token = $token;
        $user->password_reset_at = now();
        $user->save();

        $resetUrl = route("{$this->userType}.password.reset", ['hash' => $token]);
        $message = __('emails.reset_password_body') . "\n\n" . $resetUrl;

        Mail::raw($message, function($mail) use ($user) {
            $mail->to($user->email)
                ->subject(__('emails.reset_password_subject'));
        });

        return back()->with('status', __('auth.email_password_reset_sent'));
    }

    public function verify(Request $request)
    {
        $user = $this->model::where('remember_token', $request->route('hash'))->firstOrFail();

        if (!hash_equals((string) $request->route('hash'), $user->remember_token)) {
            throw new AuthenticationException(__('messages_verification_required'));
        }

        if ($user->is_verified) {
            return redirect()->route("{$this->userType}.login")
                ->with('success', __('messages_email_already_verified'));
        }

        $user->is_active = true;
        $user->is_verified = true;
        $user->remember_token = null;
        $user->save();

        return redirect()->route("{$this->userType}.login")
            ->with('success', __('auth.messages_verification_success'));
    }

    public function resend(Request $request)
    {
        $user = $request->user($this->guard);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => __('auth.user_not_found')
            ]);
        }

        if (RateLimiter::tooManyAttempts('verify:'.$user->id, 3)) {
            return back()->with('error', __('messages_verification_check'));
        }

        if (!$user->is_verified) {
            $user->remember_token = sha1(Str::random(40));
            $user->save();

            $verificationUrl = route("{$this->userType}.verification.verify", [
                'hash' => $user->remember_token
            ]);
            
            $message = __('emails.verify_email_body') . "\n\n" . $verificationUrl;

            Mail::raw($message, function($mail) use ($user) {
                $mail->to($user->email)
                    ->subject(__('emails.verify_email_subject')); 
            });

            RateLimiter::hit('verify:'.$user->id);

            return back()
                ->with('error', __('auth.must_verify_first'))
                ->with('resent', true)
                ->with('success', __('messages_verification_sent'));
        }

        return redirect()->route("{$this->userType}.dashboard")
            ->with('success', __('messages_email_already_verified'));
    }

    public function showVerificationNotice()
    {
        $user = Auth::guard($this->guard)->user();
        
        if (!$user) {
            return redirect()->route("{$this->userType}.login");
        }

        return $user->is_verified
            ? redirect()->route("{$this->userType}.dashboard")
            : view('frontend.auth.verify', [
                'userType' => $this->userType
            ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $user = $this->model::where('email', $request->email)->first();
        if (!$user || 
            !hash_equals($request->token, $user->remember_token) || 
            !$user->password_reset_at || 
            now()->diffInMinutes($user->password_reset_at) > 60) {
            throw ValidationException::withMessages([
                'email' => __('passwords.token')
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_at = null;
        $user->remember_token = null;
        $user->save();

        return redirect()->route("{$this->userType}.login")
            ->with('success', __('passwords.reset'));
    }

    public function showRegistrationSuccess()
    {
        if (!session('registration_success')) {
            return redirect()->route("{$this->userType}.register");
        }

        return view('frontend.auth.register-success', [
            'email' => session('registered_email'),
            'userType' => $this->userType
        ]);
    }
}
