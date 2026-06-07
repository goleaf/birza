<?php

namespace App\Livewire\Backend\Settings;

use App\Models\GlobalSettings;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public float $portal_additional_price = 0.0;

    public string $admin_primary_color = GlobalSettings::DEFAULT_ADMIN_PRIMARY_COLOR;

    public string $admin_accent_color = GlobalSettings::DEFAULT_ADMIN_ACCENT_COLOR;

    public string $admin_surface_color = GlobalSettings::DEFAULT_ADMIN_SURFACE_COLOR;

    public array $admin_spotlight_tags = [];

    public function mount(): void
    {
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

    public function save(): void
    {
        $validated = $this->validate([
            'portal_additional_price' => ['required', 'numeric', 'min:0'],
            'admin_primary_color' => ['required', 'hex_color'],
            'admin_accent_color' => ['required', 'hex_color'],
            'admin_surface_color' => ['required', 'hex_color'],
            'admin_spotlight_tags' => ['nullable', 'array'],
            'admin_spotlight_tags.*' => ['required', 'string', 'max:50'],
        ]);

        $validated['admin_spotlight_tags'] = collect($validated['admin_spotlight_tags'] ?? [])
            ->map(fn (mixed $tag): string => trim((string) $tag))
            ->filter()
            ->values()
            ->all();

        $settings = GlobalSettings::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            GlobalSettings::create($validated);
        }

        Cache::forget('portal_additional_price');
        Cache::forget('admin_theme_colors');
        Cache::forget('admin_spotlight_tags');

        session()->flash('success', __('messages_settings_updated_success'));
    }

    public function render()
    {
        return view('backend.settings.index');
    }
}
