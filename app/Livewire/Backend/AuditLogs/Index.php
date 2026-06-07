<?php

namespace App\Livewire\Backend\AuditLogs;

use App\Models\AuditLog;
use App\Models\BuyerCreditHistory;
use App\Models\GlobalSettings;
use App\Models\Order;
use App\Models\Product;
use App\Models\Users\Buyer;
use App\Models\Users\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Url(except: '')]
    public string $action = '';

    #[Url(as: 'actor', except: '')]
    public string $actorId = '';

    #[Url(except: '')]
    public string $role = '';

    #[Url(as: 'entity', except: '')]
    public string $entityType = '';

    #[Url(as: 'entity_id', except: '')]
    public string $entityId = '';

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    public bool $drawer = false;

    public int $perPage = 25;

    public function mount(): void
    {
        $this->authorize('viewAny', AuditLog::class);
    }

    public function clear(): void
    {
        $this->reset('action', 'actorId', 'role', 'entityType', 'entityId', 'dateFrom', 'dateTo');
        $this->perPage = 25;
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if ($property === 'drawer') {
            return;
        }

        $this->resetPage();
    }

    public function render(): View
    {
        $logs = AuditLog::query()
            ->select([
                'id',
                'actor_id',
                'actor_type',
                'actor_role',
                'action',
                'auditable_id',
                'auditable_type',
                'reason',
                'ip_address',
                'created_at',
            ])
            ->with(['actor', 'auditable'])
            ->when($this->action !== '', fn ($query) => $query->action($this->action))
            ->when($this->role !== '', fn ($query) => $query->role($this->role))
            ->when($this->actorId !== '', fn ($query) => $query->where('actor_id', (int) $this->actorId))
            ->when($this->entityType !== '', fn ($query) => $query->entity($this->entityType, $this->entityId !== '' ? (int) $this->entityId : null))
            ->when($this->dateFrom !== '', fn ($query) => $query->createdFrom($this->dateFrom))
            ->when($this->dateTo !== '', fn ($query) => $query->createdUntil($this->dateTo))
            ->latest('created_at')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.backend.audit-logs.index', [
            'actionOptions' => $this->actionOptions(),
            'entityOptions' => $this->entityOptions(),
            'headers' => $this->headers(),
            'logs' => $logs,
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    /**
     * @return array<int, array{key: string, label: string, sortable?: bool}>
     */
    private function headers(): array
    {
        return [
            ['key' => 'created_at', 'label' => __('audit_logs.created_at')],
            ['key' => 'action', 'label' => __('audit_logs.action')],
            ['key' => 'actor', 'label' => __('audit_logs.actor'), 'sortable' => false],
            ['key' => 'actor_role', 'label' => __('audit_logs.actor_role')],
            ['key' => 'auditable', 'label' => __('audit_logs.auditable'), 'sortable' => false],
            ['key' => 'reason', 'label' => __('audit_logs.reason'), 'sortable' => false],
        ];
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    private function actionOptions(): array
    {
        return AuditLog::query()
            ->select(['action'])
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(fn (string $action): array => ['id' => $action, 'name' => $action])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    private function roleOptions(): array
    {
        return AuditLog::query()
            ->select(['actor_role'])
            ->distinct()
            ->orderBy('actor_role')
            ->pluck('actor_role')
            ->map(fn (string $role): array => ['id' => $role, 'name' => $role])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    private function entityOptions(): array
    {
        return collect([
            Product::class,
            Order::class,
            Buyer::class,
            Seller::class,
            BuyerCreditHistory::class,
            GlobalSettings::class,
        ])
            ->map(fn (string $class): array => ['id' => $class, 'name' => class_basename($class)])
            ->values()
            ->all();
    }
}
