<?php

namespace App\Livewire\Backend\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.backend.auth')]
#[Title('Admin Login')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::guard('admin')->check()) {
            $this->redirectRoute('admin.dashboard');
        }
    }

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $this->remember)) {
            $user = Auth::guard('admin')->user();

            if (! $user?->is_active) {
                Auth::guard('admin')->logout();

                throw ValidationException::withMessages([
                    'email' => __('messages_account_inactive'),
                ]);
            }

            session()->regenerate();

            $this->redirectIntended(route('admin.dashboard'));

            return;
        }

        $this->reset('password');

        throw ValidationException::withMessages([
            'email' => __('auth_failed'),
        ]);
    }

    public function render()
    {
        return view('livewire.backend.auth.login');
    }
}
