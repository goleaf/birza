@php
    $activeLocale = in_array(app()->getLocale(), $locales, true) ? app()->getLocale() : ($locales[0] ?? 'en');
@endphp

<x-backend.page :title="$country->exists ? __('backend.countries.edit') : __('backend.countries.create')">
    <x-ui.card>
        <form wire:submit.prevent="save" class="space-y-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-4 lg:col-span-1">
                        <div>
                            <h3 class="text-lg font-semibold">{{ __('common.basic_information') }}</h3>
                        </div>

                        <div class="form-control">
                            <label for="alpha2" class="label">
                                <span class="label-text">{{ __('backend.countries.fields.alpha2') }}</span>
                            </label>
                            <input
                                id="alpha2"
                                name="alpha2"
                                maxlength="2"
                                class="input input-bordered w-full"
                                wire:model.defer="alpha2"
                            />
                        </div>

                        <div class="form-control">
                            <label for="region" class="label">
                                <span class="label-text">{{ __('backend.countries.fields.region') }}</span>
                            </label>
                            <select
                                id="region"
                                name="region"
                                class="select select-bordered w-full"
                                wire:model.defer="region"
                            >
                                @foreach ($regionOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="label cursor-pointer justify-start gap-3">
                            <input
                                id="is_active"
                                name="is_active"
                                type="checkbox"
                                class="toggle toggle-primary"
                                wire:model.defer="is_active"
                            />
                            <span class="label-text">{{ __('backend.countries.fields.is_active') }}</span>
                        </label>
                    </div>

                    <div class="space-y-4 lg:col-span-2">
                        <div>
                            <h3 class="text-lg font-semibold">{{ __('backend.countries.translations') }}</h3>
                        </div>

                        <div class="space-y-4" x-data="{ locale: '{{ $activeLocale }}' }">
                            <div role="tablist" class="tabs tabs-boxed">
                                @foreach ($locales as $locale)
                                    <button
                                        type="button"
                                        role="tab"
                                        class="tab"
                                        :class="locale === '{{ $locale }}' ? 'tab-active' : ''"
                                        @click="locale = '{{ $locale }}'"
                                    >
                                        {{ strtoupper($locale) }}
                                    </button>
                                @endforeach
                            </div>

                            @foreach ($locales as $locale)
                                <div x-show="locale === '{{ $locale }}'" x-cloak>
                                    <div class="form-control">
                                        <label for="country_name_{{ $locale }}" class="label">
                                            <span class="label-text">{{ strtoupper($locale) }} {{ __('backend.countries.fields.country_name') }}</span>
                                        </label>
                                        <input
                                            id="country_name_{{ $locale }}"
                                            name="country_name.{{ $locale }}"
                                            class="input input-bordered w-full"
                                            wire:model.defer="country_name.{{ $locale }}"
                                        />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-ui.form-actions
                    :submit-label="$country->exists ? __('backend.common.update') : __('backend.common.create')"
                    :cancel-href="route('backend.countries.index')"
                    submit-target="save"
                />
        </form>
    </x-ui.card>
</x-backend.page>
