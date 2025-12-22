<?php

namespace App\Livewire\Backend\Attributes;

use App\Models\Attribute;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public function deleteAttribute(int $attributeId): void
    {
        Attribute::query()->findOrFail($attributeId)->delete();

        session()->flash('success', __('backend.common.delete_success'));
    }

    public function render()
    {
        return view('backend.attributes.index', [
            'attributes' => Attribute::with('values')->orderBy('id', 'desc')->paginate(20),
        ]);
    }
}


