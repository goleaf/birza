@php($isEditing = $attributeValue?->exists ?? false)

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="$isEditing
            ? [
                ['label' => __('navigation_attributes'), 'link' => route('admin.attributes.index')],
                ['label' => $attribute->getTranslation('name', app()->getLocale())],
                ['label' => __('common_values'), 'link' => route('admin.attributes.values.index', $attribute)],
                ['label' => $attributeValue?->getTranslation('value', app()->getLocale()) ?? __('common_edit')],
            ]
            : [
                ['label' => __('navigation_attributes'), 'link' => route('admin.attributes.index')],
                ['label' => $attribute->getTranslation('name', app()->getLocale())],
                ['label' => __('common_values'), 'link' => route('admin.attributes.values.index', $attribute)],
                ['label' => __('common_create')],
            ]"
    />

    <x-mary-header
        :title="$isEditing ? __('backend_attribute_values_edit_title') : __('backend_attribute_values_create_title')"
        :subtitle="$attribute->getTranslation('name', app()->getLocale())"
        separator
        progress-indicator
    />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(280px,1fr)]">
        <x-mary-form wire:submit="save" class="gap-6">
            <x-mary-card
                :title="__('backend_attribute_values_fields_value')"
                :subtitle="$attribute->getTranslation('name', app()->getLocale())"
                shadow
            >
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach (config('app.locales') as $locale)
                        <x-mary-input
                            :label="strtoupper($locale) . ' ' . __('backend_attribute_values_fields_value')"
                            wire:model="translations.{{ $locale }}"
                            required
                        />
                    @endforeach
                </div>
            </x-mary-card>

            <x-slot:actions>
                <x-mary-button
                    :label="__('backend_common_cancel')"
                    :link="route('admin.attributes.values.index', $attribute)"
                />
                <x-mary-button
                    :label="$isEditing ? __('backend_common_update') : __('backend_common_create')"
                    icon="o-paper-airplane"
                    spinner="save"
                    type="submit"
                    class="btn-primary"
                />
            </x-slot:actions>
        </x-mary-form>

        <x-mary-card :title="__('common_status')" shadow>
            <x-mary-toggle
                :label="__('backend_attribute_values_fields_is_active')"
                wire:model="is_active"
                right
            />
        </x-mary-card>
    </div>
</div>
