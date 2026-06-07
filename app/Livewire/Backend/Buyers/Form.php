<?php

namespace App\Livewire\Backend\Buyers;

use App\Models\AuditLog;
use App\Models\Users\Buyer;
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

    public bool $is_verified = true;

    public bool $is_active = true;

    public ?string $audit_reason = null;

    public function mount(?Buyer $buyer = null): void
    {
        if ($buyer) {
            $this->authorize('update', $buyer);
        } else {
            $this->authorize('create', Buyer::class);
        }

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
        $this->is_verified = (bool) ($buyer?->is_verified ?? true);
        $this->is_active = (bool) ($buyer?->is_active ?? true);
    }

    public function save(AuditLogService $auditLogService): void
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
            'is_verified' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'audit_reason' => ['nullable', 'string', 'max:500'],
        ];

        if ($isCreating) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);
        $oldValues = $this->buyer
            ? $auditLogService->snapshot($this->buyer, $this->auditedFields())
            : [];

        if (! $isCreating && $this->requiresReason($oldValues, $validated) && blank($validated['audit_reason'] ?? null)) {
            $this->addError('audit_reason', __('audit_logs.reason_required'));

            return;
        }

        $buyer = $this->buyer ?? new Buyer;
        $this->authorize($isCreating ? 'create' : 'update', $isCreating ? Buyer::class : $buyer);

        DB::transaction(function () use ($auditLogService, $buyer, $isCreating, $oldValues, $validated): void {
            $buyer->forceFill([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'company_name' => $validated['company_name'],
                'company_code' => $validated['company_code'],
                'vat_code' => $validated['vat_code'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'credit_balance' => $validated['credit_balance'] ?? 0,
                'is_verified' => (bool) ($validated['is_verified'] ?? true),
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]);

            if ($isCreating) {
                $buyer->password = $validated['password'];
            }

            $buyer->save();

            if ($isCreating) {
                $auditLogService->log(
                    actor: Auth::guard('admin')->user(),
                    action: 'buyer.created',
                    auditable: $buyer,
                    oldValues: null,
                    newValues: $auditLogService->snapshot($buyer, $this->auditedFields()),
                    metadata: ['source' => 'admin_buyer_form'],
                );

                return;
            }

            if (! $isCreating) {
                $this->logBuyerUpdate($auditLogService, $buyer->refresh(), $oldValues, $validated['audit_reason'] ?? null);
            }
        });

        session()->flash('success', __('backend_common_success_message'));
        $this->redirectRoute('admin.buyers.index');
    }

    public function render()
    {
        return view('backend.buyers.form', [
            'auditLogs' => $this->buyer
                ? AuditLog::query()
                    ->entity($this->buyer)
                    ->with('actor')
                    ->latest('created_at')
                    ->limit(10)
                    ->get()
                : collect(),
            'buyer' => $this->buyer,
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
            'credit_balance',
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
    private function logBuyerUpdate(AuditLogService $auditLogService, Buyer $buyer, array $oldValues, ?string $reason): void
    {
        $newValues = $auditLogService->snapshot($buyer, $this->auditedFields());
        $changed = $auditLogService->changedValues($oldValues, $newValues);

        if ($changed['old'] !== [] || $changed['new'] !== []) {
            $auditLogService->log(
                actor: Auth::guard('admin')->user(),
                action: 'buyer.updated',
                auditable: $buyer,
                oldValues: $changed['old'],
                newValues: $changed['new'],
                metadata: ['source' => 'admin_buyer_form'],
                reason: $reason,
            );
        }

        if (($oldValues['is_active'] ?? null) !== ($newValues['is_active'] ?? null)) {
            $auditLogService->log(
                actor: Auth::guard('admin')->user(),
                action: $newValues['is_active'] ? 'user.unblocked' : 'user.blocked',
                auditable: $buyer,
                oldValues: ['is_active' => $oldValues['is_active'] ?? null],
                newValues: ['is_active' => $newValues['is_active'] ?? null],
                metadata: ['source' => 'admin_buyer_form'],
                reason: $reason,
            );

        }
    }
}
