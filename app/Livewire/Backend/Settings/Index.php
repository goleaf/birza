<?php

namespace App\Livewire\Backend\Settings;

use App\Models\GlobalSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public float $portal_additional_price = 0.0;

    public function mount(): void
    {
        $settings = GlobalSettings::first();

        $this->portal_additional_price = (float) ($settings->portal_additional_price ?? 0);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'portal_additional_price' => ['required', 'numeric', 'min:0'],
        ]);

        $settings = GlobalSettings::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            GlobalSettings::create($validated);
        }

        session()->flash('success', __('messages.settings_updated_success'));
    }

    public function render()
    {
        return view('backend.settings.index', [
            'settings' => GlobalSettings::first(),
        ]);
    }
}


