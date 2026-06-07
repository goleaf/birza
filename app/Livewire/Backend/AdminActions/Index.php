<?php

namespace App\Livewire\Backend\AdminActions;

use App\Models\AdminAction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public int $perPage = 20;

    public function mount(): void
    {
        $this->authorize('viewAny', AdminAction::class);
    }

    /**
     * @return list<array{key: string, label: string, class?: string}>
     */
    public function headers(): array
    {
        return [
            ['key' => 'created_at', 'label' => __('admin_actions_columns_created_at')],
            ['key' => 'actor', 'label' => __('admin_actions_columns_actor')],
            ['key' => 'action', 'label' => __('admin_actions_columns_action')],
            ['key' => 'entity', 'label' => __('admin_actions_columns_entity')],
            ['key' => 'reason', 'label' => __('admin_actions_columns_reason')],
        ];
    }

    public function render()
    {
        $actions = AdminAction::query()
            ->with(['actor:id,name,email'])
            ->latest()
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.backend.admin-actions.index', [
            'actions' => $actions,
            'headers' => $this->headers(),
        ]);
    }
}
