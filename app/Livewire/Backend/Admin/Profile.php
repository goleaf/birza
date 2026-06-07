<?php

namespace App\Livewire\Backend\Admin;

use App\Models\Users\Admin;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Profile extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $admin = $this->admin();

        $this->authorize('update', $admin);

        $this->name = (string) ($admin?->name ?? '');
        $this->email = (string) ($admin?->email ?? '');
    }

    public function saveProfile(): void
    {
        $admin = $this->admin();

        $this->authorize('update', $admin);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users_admins', 'email')->ignore($admin->id)],
        ]);

        $admin->update($validated);

        session()->flash('success', __('profile_update_success'));
    }

    public function savePassword(): void
    {
        $admin = $this->admin();

        $this->authorize('update', $admin);

        $this->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $admin->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', __('profile_password_updated'));
    }

    public function render()
    {
        return view('backend.admin.profile');
    }

    private function admin(): Admin
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }
}
