<?php

namespace App\Livewire\Concerns;

use WireUi\Traits\WireUiActions;

trait InteractsWithWireUi
{
    use WireUiActions;

    public bool $confirmModal = false;

    public string $confirmModalTitle = '';

    public string $confirmModalDescription = '';

    public string $confirmModalMethod = '';

    public string $confirmModalAcceptLabel = '';

    /**
     * @var array<int, mixed>
     */
    public array $confirmModalParams = [];

    protected function confirmAction(
        string $title,
        ?string $description,
        string $acceptLabel,
        string $method,
        mixed $params = null,
        string $icon = 'question',
        ?string $rejectLabel = null,
    ): void {
        $this->confirmModalTitle = $title;
        $this->confirmModalDescription = (is_string($description) && $description !== '') ? $description : $title;
        $this->confirmModalMethod = $method;
        $this->confirmModalAcceptLabel = $acceptLabel;
        $this->confirmModalParams = $params === null ? [] : (is_array($params) ? array_values($params) : [$params]);
        $this->confirmModal = true;
    }

    public function closeConfirmModal(): void
    {
        $this->confirmModal = false;
        $this->confirmModalTitle = '';
        $this->confirmModalDescription = '';
        $this->confirmModalMethod = '';
        $this->confirmModalAcceptLabel = '';
        $this->confirmModalParams = [];
    }

    public function runConfirmedAction(): void
    {
        $method = $this->confirmModalMethod;
        $params = $this->confirmModalParams;

        if ($method === '' || ! method_exists($this, $method)) {
            $this->closeConfirmModal();

            return;
        }

        $this->{$method}(...$params);
        $this->closeConfirmModal();
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
            title: __('common_confirm_delete'),
            description: __('common_confirm_delete'),
            acceptLabel: __('common_delete'),
            method: $method,
            params: $params,
            icon: 'warning',
            rejectLabel: __('common_cancel'),
        );
    }
}
