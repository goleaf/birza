@php($isEditing = $category?->exists ?? false)

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="$isEditing
            ? [
                ['label' => __('navigation_categories'), 'link' => route('backend.categories.index')],
                ['label' => $category->getTranslation('category_name', app()->getLocale())],
            ]
            : [
                ['label' => __('navigation_categories'), 'link' => route('backend.categories.index')],
                ['label' => __('common_create')],
            ]"
    />

    <x-mary-header
        :title="$isEditing ? __('backend_categories_edit_title') : __('backend_categories_create_title')"
        :subtitle="$isEditing ? $category->getTranslation('category_name', app()->getLocale()) : __('backend_categories_title')"
        separator
        progress-indicator
    />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(320px,1fr)]">
        <x-mary-form wire:submit="save" class="gap-6">
            <x-mary-card
                :title="__('backend_categories_title')"
                :subtitle="__('backend_categories_fields_parent_category')"
                shadow
            >
                <div class="space-y-4">
                    <x-mary-select
                        :label="__('backend_categories_fields_parent_category')"
                        wire:model="parent_category_id"
                        :options="$parentCategoryOptions"
                        option-value="id"
                        option-label="name"
                        :placeholder="__('backend_categories_select_parent')"
                        placeholder-value=""
                    />

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach (config('app.locales') as $locale)
                            <x-mary-input
                                :label="strtoupper($locale) . ' ' . __('backend_categories_fields_name')"
                                wire:model="name.{{ $locale }}"
                                required
                            />
                        @endforeach
                    </div>
                </div>
            </x-mary-card>

            <x-slot:actions>
                <x-mary-button
                    :label="__('backend_common_cancel')"
                    :link="route('backend.categories.index')"
                />
                <x-mary-button
                    :label="$isEditing ? __('backend_categories_actions_update') : __('backend_categories_actions_create')"
                    icon="o-paper-airplane"
                    spinner="save"
                    type="submit"
                    class="btn-primary"
                />
            </x-slot:actions>
        </x-mary-form>

        <x-mary-card
            :title="__('backend_categories_fields_attributes')"
            :subtitle="__('backend_categories_attributes_reset_notice')"
            shadow
        >
            <x-mary-choices-offline
                :label="__('backend_categories_fields_attributes')"
                wire:model="selectedAttributes"
                :options="$attributeOptions"
                option-value="id"
                option-label="name"
                option-sub-label="status"
                searchable
            />
        </x-mary-card>
    </div>
</div>
