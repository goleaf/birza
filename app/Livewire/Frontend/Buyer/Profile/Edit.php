<?php

namespace App\Livewire\Frontend\Buyer\Profile;

use App\Models\Users\Buyer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontend.app')]
class Edit extends Component
{
    public string $name = '';
    public string $email = '';
    public string $company_name = '';
    public string $company_code = '';
    public ?string $vat_code = null;
    public string $address = '';
    public string $phone = '';
    public string $bank_account = '';

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $this->name = (string) ($buyer?->name ?? '');
        $this->email = (string) ($buyer?->email ?? '');
        $this->company_name = (string) ($buyer?->company_name ?? '');
        $this->company_code = (string) ($buyer?->company_code ?? '');
        $this->vat_code = $buyer?->vat_code;
        $this->address = (string) ($buyer?->address ?? '');
        $this->phone = (string) ($buyer?->phone ?? '');
        $this->bank_account = (string) ($buyer?->bank_account ?? '');
    }

    public function saveProfile(): void
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users_buyers', 'email')->ignore($buyer?->id)],
            'company_name' => ['required', 'string', 'max:255'],
            'company_code' => ['required', 'string', 'max:255'],
            'vat_code' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\\+[0-9]{8,}$/'],
            'bank_account' => ['required', 'string', 'max:255'],
        ]);

        $buyer?->update($validated);

        session()->flash('success', __('profile_update_success'));
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password:buyer'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        $buyer?->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('password_success', __('profile_password_updated'));
    }

    public function render()
    {
        /** @var Buyer $buyer */
        $buyer = Auth::guard('buyer')->user();

        return view('frontend.buyer.profile.edit', [
            'buyer' => $buyer,
        ]);
    }
}


