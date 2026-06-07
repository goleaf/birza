<?php

namespace App\Livewire\Backend\Settings;

use App\Models\AuditLog;
use App\Models\GlobalSettings;
use App\Services\AuditLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use AuthorizesRequests;

    public float $portal_additional_price = 0.0;

    public string $admin_primary_color = GlobalSettings::DEFAULT_ADMIN_PRIMARY_COLOR;

    public string $admin_accent_color = GlobalSettings::DEFAULT_ADMIN_ACCENT_COLOR;

    public string $admin_surface_color = GlobalSettings::DEFAULT_ADMIN_SURFACE_COLOR;

    public array $admin_spotlight_tags = [];

    public ?string $audit_reason = null;

    public function mount(): void
    {
        $this->authorize('manage', GlobalSettings::class);

        $settings = GlobalSettings::first();

        $this->portal_additional_price = (float) ($settings->portal_additional_price ?? 0);
        $this->admin_primary_color = (string) ($settings->admin_primary_color ?? GlobalSettings::DEFAULT_ADMIN_PRIMARY_COLOR);
        $this->admin_accent_color = (string) ($settings->admin_accent_color ?? GlobalSettings::DEFAULT_ADMIN_ACCENT_COLOR);
        $this->admin_surface_color = (string) ($settings->admin_surface_color ?? GlobalSettings::DEFAULT_ADMIN_SURFACE_COLOR);
        $this->admin_spotlight_tags = collect($settings->admin_spotlight_tags ?? [])
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter()
            ->values()
            ->all();
    }

    public function save(AuditLogService $auditLogService): void
    {
        $this->authorize('manage', GlobalSettings::class);

        $validated = $this->validate([
            'portal_additional_price' => ['required', 'numeric', 'min:0'],
            'admin_primary_color' => ['required', 'hex_color'],
            'admin_accent_color' => ['required', 'hex_color'],
            'admin_surface_color' => ['required', 'hex_color'],
            'admin_spotlight_tags' => ['nullable', 'array'],
            'admin_spotlight_tags.*' => ['required', 'string', 'max:50'],
            'audit_reason' => ['required', 'string', 'max:500'],
        ]);

        $validated['admin_spotlight_tags'] = collect($validated['admin_spotlight_tags'] ?? [])
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter()
            ->values()
            ->all();

        $reason = $validated['audit_reason'];
        unset($validated['audit_reason']);

        DB::transaction(function () use ($auditLogService, $reason, $validated): void {
            $settings = GlobalSettings::query()->first();
            $oldValues = $settings ? $auditLogService->snapshot($settings, array_keys($validated)) : null;

            if ($settings) {
                $settings->update($validated);
            } else {
                $settings = GlobalSettings::query()->create($validated);
            }

            $newValues = $auditLogService->snapshot($settings->refresh(), array_keys($validated));
            $changed = $oldValues === null
                ? ['old' => null, 'new' => $newValues]
                : $auditLogService->changedValues($oldValues, $newValues);

            if ($oldValues === null || $changed['old'] !== [] || $changed['new'] !== []) {
                $auditLogService->log(
                    actor: Auth::guard('admin')->user(),
                    action: 'settings.updated',
                    auditable: $settings,
                    oldValues: $changed['old'],
                    newValues: $changed['new'],
                    metadata: ['source' => 'backend_settings'],
                    reason: $reason,
                );
            }
        });

        Cache::forget('portal_additional_price');
        Cache::forget('admin_theme_colors');
        Cache::forget('admin_spotlight_tags');
        $this->audit_reason = null;

        session()->flash('success', __('messages_settings_updated_success'));
    }

    public function render()
    {
        $settings = GlobalSettings::query()->first();

        return view('backend.settings.index', [
            'auditLogs' => $settings
                ? AuditLog::query()
                    ->entity($settings)
                    ->with('actor')
                    ->latest('created_at')
                    ->limit(10)
                    ->get()
                : collect(),
        ]);
    }
}
