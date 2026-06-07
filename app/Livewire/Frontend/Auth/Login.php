<?php

namespace App\Livewire\Frontend\Auth;

use App\Actions\Cart\MergeGuestCartAction;
use App\Models\Users\Buyer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Login extends Component
{
    public string $userType = 'buyer';

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected int $maxAttempts = 5;

    protected int $decayMinutes = 15;

    public function mount(?string $userType = null): void
    {
        $segment = $userType ?? request()->segment(1);

        if (! in_array($segment, ['buyer', 'seller'], true)) {
            abort(404);
        }

        $this->userType = $segment;

        if (Auth::guard($this->userType)->check()) {
            $this->redirectRoute("{$this->userType}.dashboard");
        }
    }

    public function login(MergeGuestCartAction $mergeGuestCartAction): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $otherGuard = $this->userType === 'buyer' ? 'seller' : 'buyer';

        if (Auth::guard($otherGuard)->check()) {
            throw ValidationException::withMessages([
                'login' => __('messages_messages_invalid_credentials'),
            ]);
        }

        $throttleKey = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('messages_too_many_login_attempts', ['seconds' => $seconds]),
            ]);
        }

        if (Auth::guard($this->userType)->attempt($credentials, $this->remember)) {
            RateLimiter::clear($throttleKey);
            session()->regenerate();

            $user = Auth::guard($this->userType)->user();

            if (! $user?->is_active) {
                Auth::guard($this->userType)->logout();

                throw ValidationException::withMessages([
                    'email' => __('messages_account_inactive'),
                ]);
            }

            if ($this->userType === 'seller' && ! $user?->is_verified) {
                Auth::guard('seller')->logout();

                throw ValidationException::withMessages([
                    'email' => __('auth_verification_required'),
                ]);
            }

            if ($this->userType === 'buyer' && $user instanceof Buyer && session()->has('cart_guest_token')) {
                $mergeGuestCartAction->handle((string) session('cart_guest_token'), $user);
                session()->forget('cart_guest_token');
            }

            if ($user && (empty($user->company_name) || empty($user->company_code) || empty($user->address) || empty($user->phone))) {
                session()->flash('warning', __('profile_complete_profile'));
                $this->redirectRoute("{$this->userType}.profile.edit");

                return;
            }

            $this->redirectIntended(route("{$this->userType}.dashboard"));

            return;
        }

        RateLimiter::hit($throttleKey, $this->decayMinutes * 60);

        $this->reset('password');

        throw ValidationException::withMessages([
            'email' => __('messages_messages_invalid_credentials'),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::lower($this->email).'|'.request()->ip();
    }

    public function render()
    {
        return view('livewire.frontend.auth.login');
    }
}
