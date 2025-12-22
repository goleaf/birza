<?php

namespace App\Livewire\Backend\Attributes;

use App\Livewire\Concerns\InteractsWithWireUi;
use App\Models\Attribute;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    use InteractsWithWireUi;

    public function confirmDeleteAttribute(int $attributeId): void
    {
        $this->confirmDelete(method: 'deleteAttribute', params: $attributeId);
    }

    public function deleteAttribute(int $attributeId): void
    {
        Attribute::query()->findOrFail($attributeId)->delete();

        $this->notifySuccess(__('backend.common.delete_success'));
    }

    public function render()
    {
        return view('backend.attributes.index', [
            'attributes' => Attribute::with('values')->orderBy('id', 'desc')->paginate(20),
        ]);
    }
}


