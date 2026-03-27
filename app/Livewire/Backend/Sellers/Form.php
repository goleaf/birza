<?php

namespace App\Livewire\Backend\Sellers;

use App\Models\Users\Seller;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    public ?Seller $seller = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $company_name = '';
    public string $company_code = '';
    public ?string $vat_code = null;
    public ?string $address = null;
    public ?string $phone = null;
    public ?string $bank_account = null;
    public ?string $veterinary_certificate_number = null;

    public bool $is_verified = true;
    public bool $is_active = true;

    public function mount(?Seller $seller = null): void
    {
        $this->seller = $seller;

        $this->name = (string) ($seller?->name ?? '');
        $this->email = (string) ($seller?->email ?? '');

        $this->company_name = (string) ($seller?->company_name ?? '');
        $this->company_code = (string) ($seller?->company_code ?? '');
        $this->vat_code = $seller?->vat_code;
        $this->address = $seller?->address;
        $this->phone = $seller?->phone;
        $this->bank_account = $seller?->bank_account;
        $this->veterinary_certificate_number = $seller?->veterinary_certificate_number;

        $this->is_verified = (bool) ($seller?->is_verified ?? true);
        $this->is_active = (bool) ($seller?->is_active ?? true);
    }

    public function save(): void
    {
        $sellerId = $this->seller?->id;
        $isCreating = $this->seller === null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users_sellers', 'email')->ignore($sellerId)],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_code' => ['nullable', 'string', 'max:255'],
            'vat_code' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'veterinary_certificate_number' => ['nullable', 'string', 'max:255'],
            'is_verified' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($isCreating) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        $seller = $this->seller ?? new Seller();

        $seller->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_name' => $validated['company_name'] ?? null,
            'company_code' => $validated['company_code'] ?? null,
            'vat_code' => $validated['vat_code'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'veterinary_certificate_number' => $validated['veterinary_certificate_number'] ?? null,
            'is_verified' => (bool) ($validated['is_verified'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        if ($isCreating) {
            $seller->password = $validated['password'];
        }

        $seller->save();

        session()->flash('success', __('backend_common_success_message'));
        $this->redirectRoute('backend.sellers.index');
    }

    public function render()
    {
        return view('backend.sellers.form', [
            'seller' => $this->seller,
        ]);
    }
}


