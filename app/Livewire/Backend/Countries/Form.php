<?php

namespace App\Livewire\Backend\Countries;

use App\Models\Country;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    use AuthorizesRequests;

    public Country $country;

    public string $alpha2 = '';

    public string $region = '';

    public string $selectedTranslationTab = '';

    public bool $is_active = true;

    /**
     * @var array<string, string>
     */
    public array $country_name = [];

    /**
     * @var list<string>
     */
    public array $locales = [];

    public function mount(?Country $country = null): void
    {
        $this->authorize($country instanceof Country ? 'update' : 'create', $country ?? Country::class);

        $this->country = $country ?? new Country;
        $this->locales = $this->configuredLocales();

        $this->alpha2 = (string) ($this->country->alpha2 ?? '');
        $this->region = (string) ($this->country->region ?? '');
        $this->is_active = (bool) ($this->country->is_active ?? true);
        $this->selectedTranslationTab = 'country-name-'.($this->locales[0] ?? config('app.locale'));

        foreach ($this->locales as $locale) {
            $this->country_name[$locale] = (string) ($this->country->getTranslation('country_name', $locale) ?? '');
        }
    }

    public function save(): void
    {
        $this->authorize($this->country->exists ? 'update' : 'create', $this->country->exists ? $this->country : Country::class);

        $countryId = $this->country->exists ? $this->country->id : null;

        $rules = [
            'alpha2' => ['required', 'string', 'max:2', 'lowercase', Rule::unique('countries', 'alpha2')->ignore($countryId)],
            'region' => ['required', Rule::in(Country::getRegionValues())],
            'is_active' => ['sometimes', 'boolean'],
        ];

        foreach ($this->locales as $locale) {
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
        $this->redirectRoute('admin.countries.index');
    }

    public function render()
    {
        return view('backend.countries.form', [
            'country' => $this->country,
            'regionOptions' => Country::getRegionOptions(),
            'locales' => $this->locales,
        ]);
    }

    /**
     * @return list<string>
     */
    private function configuredLocales(): array
    {
        return array_values((array) config('app.locales', [config('app.locale')]));
    }
}
