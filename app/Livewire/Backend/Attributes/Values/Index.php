<?php

namespace App\Livewire\Backend\Attributes\Values;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Index extends Component
{
    public Attribute $attribute;

    public function mount(Attribute $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function deleteValue(int $valueId): void
    {
        $value = $this->attribute->values()->findOrFail($valueId);
        $value->delete();

        session()->flash('success', __('backend.common.delete_success'));
    }

    public function render()
    {
        return view('backend.attributes.values.index', [
            'attribute' => $this->attribute,
            'values' => $this->attribute->values()->get(),
        ]);
    }
}


