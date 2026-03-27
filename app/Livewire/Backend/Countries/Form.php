<?php

namespace App\Livewire\Backend\Countries;

use App\Models\Country;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    public Country $country;

    public string $alpha2 = '';
    public string $region = '';
    public bool $is_active = true;
    public array $country_name = [];

    public function mount(?Country $country = null): void
    {
        $this->country = $country ?? new Country();

        $this->alpha2 = (string) ($this->country->alpha2 ?? '');
        $this->region = (string) ($this->country->region ?? '');
        $this->is_active = (bool) ($this->country->is_active ?? true);

        foreach (config('app.locales') as $locale) {
            $this->country_name[$locale] = (string) ($this->country->getTranslation('country_name', $locale) ?? '');
        }
    }

    public function save(): void
    {
        $countryId = $this->country->exists ? $this->country->id : null;

        $rules = [
            'alpha2' => ['required', 'string', 'max:2', 'lowercase', Rule::unique('countries', 'alpha2')->ignore($countryId)],
            'region' => ['required', Rule::in(Country::getRegionValues())],
            'is_active' => ['sometimes', 'boolean'],
        ];

        foreach (config('app.locales') as $locale) {
            $rules["country_name.$locale"] = ['required', 'string', 'max:255'];
        }

        $validated = $this->validate($rules);

        $this->country->fill([
            'alpha2' => strtolower($validated['alpha2']),
            'region' => $validated['region'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        $this->country->setTranslations('country_name', $validated['country_name']);
        $this->country->save();

        session()->flash('success', __('backend_common_success_message'));
        $this->redirectRoute('backend.countries.index');
    }

    public function render()
    {
        return view('backend.countries.form', [
            'country' => $this->country,
            'regionOptions' => Country::getRegionOptions(),
            'locales' => config('app.locales'),
        ]);
    }
}

