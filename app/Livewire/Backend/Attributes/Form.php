<?php

namespace App\Livewire\Backend\Attributes;

use App\Models\Attribute;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    public ?Attribute $attribute = null;

    public array $name = [];

    public string $type = 'select';

    public bool $is_filterable = false;

    public bool $is_required = false;

    public bool $is_active = true;

    public function mount(?Attribute $attribute = null): void
    {
        $this->attribute = $attribute;

        foreach (config('app.locales') as $locale) {
            $this->name[$locale] = (string) ($attribute?->getTranslation('name', $locale) ?? '');
        }

        $this->type = (string) ($attribute?->type ?? 'select');
        $this->is_filterable = (bool) ($attribute?->is_filterable ?? false);
        $this->is_required = (bool) ($attribute?->is_required ?? false);
        $this->is_active = (bool) ($attribute?->is_active ?? true);
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'array'],
            'name.*' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Attribute::TYPES))],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        $validated = $this->validate($rules);

        $attribute = $this->attribute ?? new Attribute;

        $attribute->type = $validated['type'];
        $attribute->is_active = (bool) ($validated['is_active'] ?? false);

        if (! $attribute->is_active) {
            $attribute->is_filterable = false;
            $attribute->is_required = false;
        } else {
            $attribute->is_filterable = (bool) ($validated['is_filterable'] ?? false);
            $attribute->is_required = (bool) ($validated['is_required'] ?? false);
        }

        $attribute->setTranslations('name', $validated['name']);
        $attribute->save();

        session()->flash('success', __('backend_common_success_message'));

        // After creating, go straight to values list; after editing, stay in list.
        $this->redirectRoute('backend.attributes.values.index', ['attribute' => $attribute->id]);
    }

    public function render()
    {
        return view('backend.attributes.form', [
            'attribute' => $this->attribute,
            'locales' => config('app.locales'),
            'typeOptions' => collect(Attribute::TYPES)
                ->map(fn (string $label, string $type) => [
                    'id' => $type,
                    'name' => __('backend_attributes_types_'.$type),
                ])
                ->values()
                ->all(),
        ]);
    }
}
