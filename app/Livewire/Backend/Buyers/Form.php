<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\Users\Buyer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    public ?Buyer $buyer = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public ?string $company_name = null;
    public ?string $company_code = null;
    public ?string $vat_code = null;
    public ?string $address = null;
    public ?string $phone = null;
    public ?string $bank_account = null;
    public float $credit_balance = 0.0;

    public function mount(?Buyer $buyer = null): void
    {
        $this->buyer = $buyer;

        $this->name = (string) ($buyer?->name ?? '');
        $this->email = (string) ($buyer?->email ?? '');
        $this->company_name = $buyer?->company_name;
        $this->company_code = $buyer?->company_code;
        $this->vat_code = $buyer?->vat_code;
        $this->address = $buyer?->address;
        $this->phone = $buyer?->phone;
        $this->bank_account = $buyer?->bank_account;
        $this->credit_balance = (float) ($buyer?->credit_balance ?? 0);
    }

    public function save(): void
    {
        $buyerId = $this->buyer?->id;
        $isCreating = $this->buyer === null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users_buyers', 'email')->ignore($buyerId)],
            'company_name' => ['required', 'string', 'max:255'],
            'company_code' => ['required', 'string', 'max:255'],
            'vat_code' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'credit_balance' => ['nullable', 'numeric', 'min:0'],
        ];

        if ($isCreating) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        $buyer = $this->buyer ?? new Buyer();

        $buyer->fill([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'company_name' => $validated['company_name'],
            'company_code' => $validated['company_code'],
            'vat_code' => $validated['vat_code'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'credit_balance' => $validated['credit_balance'] ?? 0,
        ]);

        if ($isCreating) {
            $buyer->password = $validated['password'];
            $buyer->is_verified = true;
            $buyer->is_active = true;
        }

        $buyer->save();

        session()->flash('success', __('backend_common_success_message'));
        $this->redirectRoute('backend.buyers.index');
    }

    public function render()
    {
        return view('backend.buyers.form', [
            'buyer' => $this->buyer,
        ]);
    }
}


