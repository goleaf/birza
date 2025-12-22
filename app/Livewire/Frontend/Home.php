<?php

namespace App\Livewire\Frontend;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.blank')]
class Home extends Component
{
    public function mount(): void
    {
        foreach (['seller', 'buyer'] as $guard) {
            if (! Auth::guard($guard)->check()) {
                continue;
            }

            $user = Auth::guard($guard)->user();

            if ($user?->is_active) {
                $this->redirectRoute("$guard.dashboard", navigate: true);
                return;
            }

            Auth::guard($guard)->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            session()->flash('error', __('messages.account_deactivated'));
            $this->redirectRoute('home', navigate: true);
            return;
        }
    }

    public function render()
    {
        return view('frontend.welcome');
    }
}


