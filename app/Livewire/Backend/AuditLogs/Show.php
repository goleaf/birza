<?php

namespace App\Livewire\Backend\AuditLogs;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Show extends Component
{
    use AuthorizesRequests;

    public AuditLog $auditLog;

    public function mount(AuditLog $auditLog): void
    {
        $this->authorize('view', $auditLog);

        $this->auditLog = $auditLog->load(['actor', 'auditable']);
    }

    public function render(): View
    {
        return view('livewire.backend.audit-logs.show', [
            'auditLog' => $this->auditLog,
            'details' => $this->details(),
            'payloads' => $this->payloads(),
        ]);
    }

    /**
     * @return array<int, array{label: string, value: string|null}>
     */
    private function details(): array
    {
        return [
            ['label' => __('audit_logs.action'), 'value' => $this->auditLog->action],
            ['label' => __('audit_logs.actor'), 'value' => $this->auditLog->actorLabel()],
            ['label' => __('audit_logs.actor_role'), 'value' => $this->auditLog->actor_role],
            ['label' => __('audit_logs.auditable'), 'value' => $this->auditLog->auditableLabel()],
            ['label' => __('audit_logs.reason'), 'value' => $this->auditLog->reason],
            ['label' => __('audit_logs.ip_address'), 'value' => $this->auditLog->ip_address],
            ['label' => __('audit_logs.user_agent'), 'value' => $this->auditLog->user_agent],
            ['label' => __('audit_logs.created_at'), 'value' => $this->auditLog->created_at?->format('Y-m-d H:i:s')],
        ];
    }

    /**
     * @return array<int, array{title: string, content: string}>
     */
    private function payloads(): array
    {
        return [
            [
                'title' => __('audit_logs.old_values'),
                'content' => $this->formatPayload($this->auditLog->old_values),
            ],
            [
                'title' => __('audit_logs.new_values'),
                'content' => $this->formatPayload($this->auditLog->new_values),
            ],
            [
                'title' => __('audit_logs.metadata'),
                'content' => $this->formatPayload($this->auditLog->metadata),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function formatPayload(?array $payload): string
    {
        if ($payload === null || $payload === []) {
            return __('common_not_specified');
        }

        return (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
