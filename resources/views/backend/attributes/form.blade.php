@php($isEditing = $attribute?->exists ?? false)

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="$isEditing
            ? [
                ['label' => __('navigation_attributes'), 'link' => route('admin.attributes.index')],
                ['label' => $attribute?->getTranslation('name', app()->getLocale()) ?? __('common_edit')],
            ]
            : [
                ['label' => __('navigation_attributes'), 'link' => route('admin.attributes.index')],
                ['label' => __('common_create')],
            ]"
    />

    <x-mary-header
        :title="$isEditing ? __('backend_attributes_edit_title') : __('backend_attributes_create_title')"
        :subtitle="$isEditing ? ($attribute?->getTranslation('name', app()->getLocale()) ?? __('backend_attributes_title')) : __('backend_attributes_title')"
        separator
        progress-indicator
    />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,1fr)]">
        <x-mary-form wire:submit="save" class="gap-6">
            <x-mary-card
                :title="__('backend_attributes_fields_name')"
                :subtitle="__('backend_attributes_fields_type')"
                shadow
            >
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($locales as $locale)
                        <x-mary-input
                            :label="strtoupper($locale) . ' ' . __('backend_attributes_fields_name')"
                            wire:model="name.{{ $locale }}"
                            required
                        />
                    @endforeach
                </div>
            </x-mary-card>

            <x-slot:actions>
                <x-mary-button
                    :label="__('backend_common_cancel')"
                    :link="route('admin.attributes.index')"
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

        <x-mary-card
            :title="__('backend_attributes_fields_type')"
            :subtitle="__('common_status')"
            shadow
        >
            <div class="space-y-4">
                <x-mary-radio
                    :label="__('backend_attributes_fields_type')"
                    wire:model="type"
                    :options="$typeOptions"
                    option-value="id"
                    option-label="name"
                    inline
                    required
                />

                <x-mary-toggle
                    :label="__('backend_attributes_fields_is_filterable')"
                    wire:model="is_filterable"
                    right
                />

                <x-mary-toggle
                    :label="__('backend_attributes_fields_is_required')"
                    wire:model="is_required"
                    right
                />

                <x-mary-toggle
                    :label="__('backend_attributes_fields_is_active')"
                    wire:model="is_active"
                    right
                />
            </div>
        </x-mary-card>
    </div>
</div>
