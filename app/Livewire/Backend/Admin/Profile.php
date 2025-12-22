<?php

namespace App\Livewire\Backend\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Profile extends Component
{
    public string $name = '';
    public string $email = '';

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $admin = Auth::guard('admin')->user();

        $this->name = (string) ($admin?->name ?? '');
        $this->email = (string) ($admin?->email ?? '');
    }

    public function saveProfile(): void
    {
        $admin = Auth::guard('admin')->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users_admins', 'email')->ignore($admin?->id)],
        ]);

        $admin?->update($validated);

        session()->flash('success', __('profile.update_success'));
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin = Auth::guard('admin')->user();

        $admin?->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', __('profile.password_updated'));
    }

    public function render()
    {
        return view('backend.admin.profile');
    }
}


