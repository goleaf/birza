<?php

namespace App\Livewire\Backend\Sellers;

use App\Models\Users\Seller;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    use AuthorizesRequests;

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

    public ?string $audit_reason = null;

    public function mount(?Seller $seller = null): void
    {
        if ($seller) {
            $this->authorize('update', $seller);
        } else {
            $this->authorize('create', Seller::class);
        }

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

    public function save(AuditLogService $auditLogService): void
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
            'audit_reason' => ['nullable', 'string', 'max:500'],
        ];

        if ($isCreating) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);
        $oldValues = $this->seller
            ? $auditLogService->snapshot($this->seller, $this->auditedFields())
            : [];

        if (! $isCreating && $this->requiresReason($oldValues, $validated) && blank($validated['audit_reason'] ?? null)) {
            $this->addError('audit_reason', __('audit_logs.reason_required'));

            return;
        }

        $seller = $this->seller ?? new Seller;
        $this->authorize($isCreating ? 'create' : 'update', $isCreating ? Seller::class : $seller);

        DB::transaction(function () use ($auditLogService, $seller, $isCreating, $oldValues, $validated): void {
            $seller->forceFill([
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

            if ($isCreating) {
                $auditLogService->log(
                    actor: Auth::guard('admin')->user(),
                    action: 'seller.created',
                    auditable: $seller,
                    oldValues: null,
                    newValues: $auditLogService->snapshot($seller, $this->auditedFields()),
                    metadata: ['source' => 'admin_seller_form'],
                );

                return;
            }

            if (! $isCreating) {
                $this->logSellerUpdate($auditLogService, $seller->refresh(), $oldValues, $validated['audit_reason'] ?? null);
            }
        });

        session()->flash('success', __('backend_common_success_message'));
        $this->redirectRoute('admin.sellers.index');
    }

    public function render()
    {
        return view('backend.sellers.form', [
            'seller' => $this->seller,
        ]);
    }

    /**
     * @return list<string>
     */
    private function auditedFields(): array
    {
        return [
            'name',
            'email',
            'company_name',
            'company_code',
            'vat_code',
            'address',
            'phone',
            'bank_account',
            'veterinary_certificate_number',
            'is_verified',
            'is_active',
            'deleted_at',
        ];
    }

    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $validated
     */
    private function requiresReason(array $oldValues, array $validated): bool
    {
        return ((bool) ($oldValues['is_active'] ?? true) !== (bool) ($validated['is_active'] ?? true))
            || ((bool) ($oldValues['is_verified'] ?? true) !== (bool) ($validated['is_verified'] ?? true));
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    private function logSellerUpdate(AuditLogService $auditLogService, Seller $seller, array $oldValues, ?string $reason): void
    {
        $newValues = $auditLogService->snapshot($seller, $this->auditedFields());
        $changed = $auditLogService->changedValues($oldValues, $newValues);

        if ($changed['old'] !== [] || $changed['new'] !== []) {
            $auditLogService->log(
                actor: Auth::guard('admin')->user(),
                action: 'seller.updated',
                auditable: $seller,
                oldValues: $changed['old'],
                newValues: $changed['new'],
                metadata: ['source' => 'admin_seller_form'],
                reason: $reason,
            );
        }

        if (($oldValues['is_verified'] ?? null) !== ($newValues['is_verified'] ?? null)) {
            $auditLogService->log(
                actor: Auth::guard('admin')->user(),
                action: $newValues['is_verified'] ? 'seller.approved' : 'seller.rejected',
                auditable: $seller,
                oldValues: ['is_verified' => $oldValues['is_verified'] ?? null],
                newValues: ['is_verified' => $newValues['is_verified'] ?? null],
                metadata: ['source' => 'admin_seller_form'],
                reason: $reason,
            );

        }

        if (($oldValues['is_active'] ?? null) !== ($newValues['is_active'] ?? null)) {
            $auditLogService->log(
                actor: Auth::guard('admin')->user(),
                action: $newValues['is_active'] ? 'user.unblocked' : 'user.blocked',
                auditable: $seller,
                oldValues: ['is_active' => $oldValues['is_active'] ?? null],
                newValues: ['is_active' => $newValues['is_active'] ?? null],
                metadata: ['source' => 'admin_seller_form'],
                reason: $reason,
            );

        }
    }
}
