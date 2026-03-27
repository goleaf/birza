<?php

namespace App\Livewire\Frontend\Auth;

use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class ResetPassword extends Component
{
    public string $userType = 'buyer';

    public string $token = '';
    public string $email = '';

    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $hash): void
    {
        $segment = request()->segment(1);

        if (! in_array($segment, ['buyer', 'seller'], true)) {
            abort(404);
        }

        $this->userType = $segment;
        $this->token = $hash;

        $modelClass = $this->userType === 'buyer' ? Buyer::class : Seller::class;
        $user = $modelClass::where('remember_token', $hash)->first();

        if (! $user || ! $user->password_reset_at || now()->diffInMinutes($user->password_reset_at) > 60) {
            session()->flash('error', __('passwords_token'));
            $this->redirectRoute("{$this->userType}.password.request", navigate: true);
            return;
        }

        $this->email = (string) $user->email;
    }

    public function resetPassword(): void
    {
        $validated = $this->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $modelClass = $this->userType === 'buyer' ? Buyer::class : Seller::class;
        $user = $modelClass::where('email', strtolower($validated['email']))->first();

        if (! $user
            || ! hash_equals((string) $validated['token'], (string) $user->remember_token)
            || ! $user->password_reset_at
            || now()->diffInMinutes($user->password_reset_at) > 60
        ) {
            throw ValidationException::withMessages([
                'email' => __('passwords_token'),
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->password_reset_at = null;
        $user->remember_token = null;
        $user->save();

        session()->flash('success', __('passwords_reset'));
        $this->redirectRoute("{$this->userType}.login", navigate: true);
    }

    public function render()
    {
        return view('livewire.frontend.auth.reset-password');
    }
}


