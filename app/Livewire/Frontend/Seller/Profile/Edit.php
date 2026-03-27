<?php

namespace App\Livewire\Frontend\Seller\Profile;

use App\Models\Category;
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
    public string $veterinary_certificate_number = '';

    public array $selectedCategories = [];

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $seller = Auth::guard('seller')->user();

        $this->name = (string) ($seller?->name ?? '');
        $this->email = (string) ($seller?->email ?? '');
        $this->company_name = (string) ($seller?->company_name ?? '');
        $this->company_code = (string) ($seller?->company_code ?? '');
        $this->vat_code = $seller?->vat_code;
        $this->address = (string) ($seller?->address ?? '');
        $this->phone = (string) ($seller?->phone ?? '');
        $this->bank_account = (string) ($seller?->bank_account ?? '');
        $this->veterinary_certificate_number = (string) ($seller?->veterinary_certificate_number ?? '');

        $this->selectedCategories = $seller?->categories->pluck('id')->all() ?? [];
    }

    public function saveProfile(): void
    {
        $seller = Auth::guard('seller')->user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users_sellers', 'email')->ignore($seller?->id)],
            'company_name' => ['required', 'string', 'max:255'],
            'company_code' => ['required', 'string', 'max:255'],
            'vat_code' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\\+[0-9]{8,}$/'],
            'bank_account' => ['required', 'string', 'max:255'],
            'veterinary_certificate_number' => ['required', 'string', 'max:255'],
        ]);

        $seller?->update($validated);

        session()->flash('success', __('profile_update_success'));
    }

    public function saveCategories(): void
    {
        $validated = $this->validate([
            'selectedCategories' => ['required', 'array'],
            'selectedCategories.*' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
                function ($attribute, $value, $fail) {
                    $category = Category::withCount('subcategories')->find($value);
                    if (! $category) {
                        $fail(__('validation_category_invalid'));
                        return;
                    }

                    if ($category->parent_category_id === null && $category->subcategories_count > 0) {
                        $fail(__('validation_category_no_parent_categories'));
                    }
                },
            ],
        ], [
            'selectedCategories.required' => __('validation_category_required'),
            'selectedCategories.array' => __('validation_category_must_be_array'),
            'selectedCategories.*.required' => __('validation_category_selection_required'),
            'selectedCategories.*.exists' => __('validation_category_must_exist'),
        ]);

        $seller = Auth::guard('seller')->user();
        $seller?->categories()->sync($validated['selectedCategories']);

        session()->flash('success', __('profile_categories_updated'));
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password:seller'],
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $seller = Auth::guard('seller')->user();

        $seller?->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('password_success', __('profile_password_updated'));
    }

    public function render()
    {
        $seller = Auth::guard('seller')->user();

        $categories = Category::with(['subcategories'])
            ->whereNull('parent_category_id')
            ->get();

        $attachedCategories = $seller->categories->pluck('id')->toArray();

        return view('frontend.seller.profile.edit', [
            'seller' => $seller,
            'categories' => $categories,
            'attachedCategories' => $attachedCategories,
        ]);
    }
}


