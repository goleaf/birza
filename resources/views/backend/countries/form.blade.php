<x-ui.page :title="$country->exists ? __('backend.countries.edit') : __('backend.countries.create')">
    <x-card>
        <form wire:submit.prevent="save" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <x-input
                         id="alpha2"
                         name="alpha2"
                         maxlength="2"
                         :label="__('backend.countries.fields.alpha2')"
                         wire:model.defer="alpha2" />

                <x-native-select
                                 id="region"
                                 name="region"
                                 :label="__('backend.countries.fields.region')"
                                 :options="$regionOptions"
                                 wire:model.defer="region" />

                <div class="flex items-end">
                    <x-toggle
                              id="is_active"
                              name="is_active"
                              :label="__('backend.countries.fields.is_active')"
                              wire:model.defer="is_active"
                              md />
                </div>
            </div>

            <div class="space-y-6">
                @foreach ($locales as $locale)
                    <x-card :title="strtoupper($locale) . ' ' . __('backend.countries.translations')">
                        <x-input
                                 :id="'country_name_' . $locale"
                                 :name="'country_name.' . $locale"
                                 :label="__('backend.countries.fields.country_name')"
                                 wire:model.defer="country_name.{{ $locale }}" />
                    </x-card>
                @endforeach
            </div>

            <x-ui.form-actions
                               :submit-label="$country->exists ? __('backend.common.update') : __('backend.common.create')"
                               :cancel-href="route('backend.countries.index')"
                               submit-target="save" />
        </form>
    </x-card>
</x-ui.page>
