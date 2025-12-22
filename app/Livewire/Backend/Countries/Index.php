<?php

namespace App\Livewire\Backend\Countries;

use App\Models\Country;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public function deleteCountry(int $countryId): void
    {
        Country::query()->findOrFail($countryId)->delete();

        session()->flash('success', __('backend.common.delete_success'));
    }

    public function render()
    {
        return view('backend.countries.index', [
            'countries' => Country::orderBy('region', 'asc')->paginate(10),
        ]);
    }
}


