@php($isEditing = $country->exists)

<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="$isEditing
            ? [
                ['label' => __('navigation_countries'), 'link' => route('backend.countries.index')],
                ['label' => strtoupper($alpha2)],
            ]
            : [
                ['label' => __('navigation_countries'), 'link' => route('backend.countries.index')],
                ['label' => __('common_create')],
            ]"
    />

    <x-mary-header
        :title="$isEditing ? __('backend_countries_edit') : __('backend_countries_create')"
        :subtitle="$isEditing ? strtoupper($alpha2) : __('backend_countries_fields_region')"
        separator
        progress-indicator
    />

    <x-mary-form wire:submit="save" class="gap-6">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_320px]">
            <x-mary-card
                :title="__('backend_countries_fields_country_name')"
                :subtitle="__('backend_countries_fields_region')"
                shadow
            >
                <div class="grid gap-4 md:grid-cols-2">
                    <x-mary-input
                        :label="__('backend_countries_fields_alpha2')"
                        wire:model="alpha2"
                        maxlength="2"
                        placeholder="LT"
                        required
                    />

                    <div class="md:col-span-2 overflow-x-auto pb-1">
                        <x-mary-radio
                            :label="__('backend_countries_fields_region')"
                            wire:model="region"
                            :options="$regionOptions"
                            option-value="value"
                            option-label="label"
                            required
                        />
                    </div>
                </div>

                <div class="mt-6">
                    <x-mary-tabs wire:model="selectedTranslationTab">
                        @foreach ($locales as $locale)
                            <x-mary-tab :name="'country-name-' . $locale" :label="strtoupper($locale)">
                                <div class="pt-4">
                                    <x-mary-input
                                        :label="__('backend_countries_fields_country_name')"
                                        wire:model="country_name.{{ $locale }}"
                                        required
                                    />
                                </div>
                            </x-mary-tab>
                        @endforeach
                    </x-mary-tabs>
                </div>
            </x-mary-card>

            <x-mary-card
                :title="__('backend_attributes_fields_status')"
                :subtitle="__('backend_countries_fields_is_active')"
                shadow
            >
                <div class="space-y-4">
                    <x-mary-toggle
                        :label="__('backend_countries_fields_is_active')"
                        wire:model="is_active"
                        right
                    />
                </div>
            </x-mary-card>
        </div>

        <x-slot:actions>
            <x-mary-button
                :label="__('backend_common_cancel')"
                :link="route('backend.countries.index')"
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
</div>
