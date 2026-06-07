<?php

namespace App\Livewire\Backend\Buyers;

use App\Livewire\Concerns\InteractsWithMaryTableSorting;
use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Users\Buyer;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithMaryTableSorting;
    use InteractsWithWireUi;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'is_verified', except: '')]
    public ?string $verifiedFilter = null;

    #[Url(as: 'is_active', except: '')]
    public ?string $activeFilter = null;

    #[Url(as: 'min_balance', except: '')]
    public ?string $minBalance = null;

    #[Url(as: 'max_balance', except: '')]
    public ?string $maxBalance = null;

    #[Url(except: 'created_at,desc')]
    public string $sort = 'created_at,desc';

    public bool $drawer = false;

    public int $perPage = 15;

    public ?string $auditReason = null;

    /**
     * @var array{column: string, direction: string}
     */
    public array $sortBy = [
        'column' => 'created_at',
        'direction' => 'desc',
    ];

    public function mount(): void
    {
        $this->sortBy = $this->sortByFromString($this->sort, ['created_at', 'name', 'company_name', 'credit_balance'], 'created_at');
        $this->sort = $this->sortString($this->sortBy);
    }

    public function confirmDeleteBuyer(int $buyerId): void
    {
        $this->auditReason = null;
        $this->confirmDelete(method: 'deleteBuyer', params: $buyerId);
    }

    public function deleteBuyer(int $buyerId, AuditLogService $auditLogService): void
    {
        $this->validateAuditReason();

        DB::transaction(function () use ($auditLogService, $buyerId): void {
            $buyer = Buyer::query()->findOrFail($buyerId);
            $oldValues = $auditLogService->snapshot($buyer, $this->auditedFields());

            $buyer->delete();

            $auditLogService->log(
                actor: Auth::guard('admin')->user(),
                action: 'buyer.deleted',
                auditable: $buyer,
                oldValues: $oldValues,
                newValues: [
                    'deleted_at' => $buyer->deleted_at?->toISOString(),
                    'is_active' => $buyer->is_active,
                ],
                metadata: ['source' => 'admin_buyer_index'],
                reason: $this->auditReason,
            );
        });

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function clear(): void
    {
        $this->reset('search', 'verifiedFilter', 'activeFilter', 'minBalance', 'maxBalance');
        $this->sortBy = [
            'column' => 'created_at',
            'direction' => 'desc',
        ];
        $this->sort = $this->sortString($this->sortBy);
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if ($property === 'drawer') {
            return;
        }

        if (str_starts_with($property, 'sortBy')) {
            $this->sortBy = $this->normalizeSortBy($this->sortBy, ['created_at', 'name', 'company_name', 'credit_balance'], 'created_at');
            $this->sort = $this->sortString($this->sortBy);
        }

        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => __('buyers_field_name')],
            ['key' => 'email', 'label' => __('buyers_field_email'), 'sortable' => false],
            ['key' => 'company_name', 'label' => __('buyers_field_company_name')],
            ['key' => 'credit_balance', 'label' => __('buyers_field_credit_balance')],
            ['key' => 'verified', 'label' => __('buyers_field_verification_status'), 'sortable' => false],
            ['key' => 'active', 'label' => __('buyers_field_active_status'), 'sortable' => false],
        ];
    }

    public function verificationOptions(): array
    {
        return [
            ['id' => 'true', 'name' => __('buyers_field_verified')],
            ['id' => 'false', 'name' => __('buyers_field_not_verified')],
        ];
    }

    public function activeOptions(): array
    {
        return [
            ['id' => 'true', 'name' => __('buyers_field_active')],
            ['id' => 'false', 'name' => __('buyers_field_inactive')],
        ];
    }

    public function render()
    {
        $query = Buyer::query()
            ->when($this->search !== '', function ($query) {
                $search = '%'.$this->search.'%';

                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('company_name', 'like', $search)
                        ->orWhere('company_code', 'like', $search)
                        ->orWhere('vat_code', 'like', $search);
                });
            })
            ->when($this->verifiedFilter !== null && $this->verifiedFilter !== '', function ($query) {
                $query->where('is_verified', $this->verifiedFilter === 'true');
            })
            ->when($this->activeFilter !== null && $this->activeFilter !== '', function ($query) {
                $query->where('is_active', $this->activeFilter === 'true');
            })
            ->when($this->minBalance !== null && $this->minBalance !== '', function ($query) {
                $query->where('credit_balance', '>=', $this->minBalance);
            })
            ->when($this->maxBalance !== null && $this->maxBalance !== '', function ($query) {
                $query->where('credit_balance', '<=', $this->maxBalance);
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        return view('backend.buyers.index', [
            'activeOptions' => $this->activeOptions(),
            'buyers' => $query->paginate($this->perPage)->withQueryString(),
            'headers' => $this->headers(),
            'verificationOptions' => $this->verificationOptions(),
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

    private function validateAuditReason(): void
    {
        $this->validate([
            'auditReason' => ['required', 'string', 'max:500'],
        ], [], [
            'auditReason' => __('audit_logs.reason'),
        ]);
    }
}
