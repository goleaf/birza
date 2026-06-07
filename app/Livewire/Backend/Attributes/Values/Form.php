<?php

namespace App\Livewire\Backend\Attributes\Values;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.backend.app')]
class Form extends Component
{
    use AuthorizesRequests;

    public Attribute $attribute;

    public ?AttributeValue $attributeValue = null;

    public array $translations = [];

    public bool $is_active = true;

    public function mount(Attribute $attribute, ?AttributeValue $value = null): void
    {
        $this->authorize('view', $attribute);
        $this->authorize($value instanceof AttributeValue ? 'update' : 'create', $value ?? AttributeValue::class);

        $this->attribute = $attribute;
        $this->attributeValue = $value;

        foreach (config('app.locales') as $locale) {
            $this->translations[$locale] = (string) ($value?->getTranslation('value', $locale) ?? '');
        }

        $this->is_active = (bool) ($value?->is_active ?? true);
    }

    public function save(): void
    {
        $this->authorize('view', $this->attribute);
        $this->authorize($this->attributeValue instanceof AttributeValue ? 'update' : 'create', $this->attributeValue ?? AttributeValue::class);

        $rules = [
            'translations' => ['required', 'array'],
            'translations.*' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        $validated = $this->validate($rules);

        $attributeValue = $this->attributeValue ?? new AttributeValue;
        $attributeValue->attribute_id = $this->attribute->id;
        $attributeValue->is_active = (bool) ($validated['is_active'] ?? false);
        $attributeValue->setTranslations('value', $validated['translations']);
        $attributeValue->save();

        session()->flash('success', __('backend_common_success_message'));
        $this->redirectRoute('admin.attributes.values.index', ['attribute' => $this->attribute->id]);
    }

    public function render()
    {
        return view('backend.attributes.values.form', [
            'attribute' => $this->attribute,
            'attributeValue' => $this->attributeValue,
        ]);
    }
}
