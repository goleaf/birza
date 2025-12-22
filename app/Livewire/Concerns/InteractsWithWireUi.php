<?php

namespace App\Livewire\Concerns;

use WireUi\Traits\WireUiActions;

trait InteractsWithWireUi
{
    use WireUiActions;

    protected function confirmAction(
        string $title,
        ?string $description,
        string $acceptLabel,
        string $method,
        mixed $params = null,
        string $icon = 'question',
        ?string $rejectLabel = null,
    ): void {
        $confirm = [
            'title' => $title,
            'description' => (is_string($description) && $description !== '') ? $description : $title,
            'icon' => $icon,
            'accept' => [
                'label' => $acceptLabel,
                'method' => $method,
            ],
            'reject' => [
                'label' => is_string($rejectLabel) && $rejectLabel !== '' ? $rejectLabel : __('common.cancel'),
            ],
        ];

        if ($params !== null) {
            $confirm['accept']['params'] = $params;
        }

        $this->notification()->confirm($confirm);
    }

    protected function notifySuccess(string $title, ?string $description = null): void
    {
        $payload = [
            'icon' => 'success',
            'title' => $title,
        ];

        if (is_string($description) && $description !== '') {
            $payload['description'] = $description;
        }

        $this->notification()->send($payload);
    }

    protected function notifyError(string $title, ?string $description = null): void
    {
        $payload = [
            'icon' => 'error',
            'title' => $title,
        ];

        if (is_string($description) && $description !== '') {
            $payload['description'] = $description;
        }

        $this->notification()->send($payload);
    }

    protected function confirmDelete(string $method, mixed $params): void
    {
        $this->confirmAction(
            title: __('common.confirm_delete'),
            description: __('common.confirm_delete'),
            acceptLabel: __('common.delete'),
            method: $method,
            params: $params,
            icon: 'warning',
            rejectLabel: __('common.cancel'),
        );
    }
}


