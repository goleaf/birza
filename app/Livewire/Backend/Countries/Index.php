<?php

namespace App\Livewire\Backend\Countries;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Country;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithWireUi;

    public function confirmDeleteCountry(int $countryId): void
    {
        $this->confirmDelete(method: 'deleteCountry', params: $countryId);
    }

    public function deleteCountry(int $countryId): void
    {
        Country::query()->findOrFail($countryId)->delete();

        $this->notifySuccess(__('backend_common_delete_success'));
    }

    public function render()
    {
        return view('backend.countries.index', [
            'countries' => Country::orderBy('region', 'asc')->paginate(10),
        ]);
    }
}


