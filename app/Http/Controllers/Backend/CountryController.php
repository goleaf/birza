<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index()
    {
        return view('backend.countries.index', [
            'countries' => Country::orderBy('region', 'asc')->paginate(10)
        ]);
    }

    public function create()
    {
        return view('backend.countries.form', [
            'country' => new Country(),
            'regionOptions' => Country::getRegionOptions(),
            'locales' => config('app.locales')
        ]);
    }

    public function store(Request $request)
    {
        $validationRules = $this->getValidationRules();
        $validatedData = $request->validate($validationRules, $this->getValidationMessages());

        $country = new Country();
        $country->fill([
            'alpha2' => strtolower($validatedData['alpha2']),
            'region' => $validatedData['region'],
            'is_active' => $validatedData['is_active'] ?? false
        ]);

        $country->save();
        $country->setTranslationData($validatedData);
        $country->save();

        return redirect()->route('backend.countries.index')
            ->with('success', __('messages.countries_created_success'));
    }

    public function edit(Country $country)
    {
        return view('backend.countries.form', [
            'country' => $country,
            'regionOptions' => Country::getRegionOptions(),
            'locales' => config('app.locales')
        ]);
    }

    public function update(Request $request, Country $country)
    {
        $validationRules = $this->getValidationRules($country->id);
        $validatedData = $request->validate($validationRules, $this->getValidationMessages());

        $country->fill([
            'alpha2' => strtolower($validatedData['alpha2']),
            'region' => $validatedData['region'],
            'is_active' => $validatedData['is_active'] ?? false
        ]);

        $country->setTranslationData($validatedData);
        $country->save();

        return redirect()->route('backend.countries.index')
            ->with('success', __('messages.countries_updated_success'));
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('backend.countries.index')
            ->with('success', __('messages.countries_deleted_success'));
    }

    private function getValidationRules($countryId = null): array
    {
        $uniqueRule = $countryId
            ? 'unique:countries,alpha2,' . $countryId
            : 'unique:countries,alpha2';

        $rules = [
            'alpha2' => ['required', 'string', 'max:2', 'lowercase', $uniqueRule],
            'region' => ['required', Rule::in(Country::getRegionOptions())],
            'is_active' => 'sometimes|boolean'
        ];

        foreach (config('app.locales') as $locale) {
            $rules[$locale . '.country_name'] = 'required|string|max:255';
            $rules[$locale . '.description'] = 'nullable|string';
        }

        return $rules;
    }

    private function getValidationMessages(): array
    {
        return [
            'alpha2.required' => __('messages.alpha2_required'),
            'alpha2.unique' => __('messages.alpha2_unique'),
            'region.required' => __('messages.region_required'),
            'region.in' => __('messages.region_invalid'),
            '*.country_name.required' => __('messages.country_name_required'),
            '*.country_name.string' => __('messages.country_name_string'),
        ];
    }
}
