@php
    $locales = config('app.locales');
    $activeLocale = in_array(app()->getLocale(), $locales, true) ? app()->getLocale() : ($locales[0] ?? 'en');
@endphp

<x-backend.page :title="isset($attribute) ? __('backend.attributes.edit.title') : __('backend.attributes.create.title')">
    <x-ui.card>
        <form wire:submit.prevent="save" class="space-y-6">
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
                            <label for="name_{{ $locale }}" class="label">
                                <span class="label-text">{{ strtoupper($locale) }} {{ __('backend.attributes.fields.name') }}</span>
                            </label>
                            <input
                                type="text"
                                id="name_{{ $locale }}"
                                wire:model.defer="name.{{ $locale }}"
                                class="input input-bordered w-full @error('name.' . $locale) input-error @enderror"
                                required
                            >
                            @error('name.' . $locale)
                                <span class="mt-1 text-sm text-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-control">
                <label for="type" class="label">
                    <span class="label-text">{{ __('backend.attributes.fields.type') }}</span>
                </label>
                <select id="type" wire:model.defer="type" class="select select-bordered w-full @error('type') select-error @enderror">
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">
                            {{ __('backend.attributes.types.' . $key) }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <span class="mt-1 text-sm text-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-3">
                <label class="flex items-center gap-3">
                    <input type="checkbox" id="is_filterable" wire:model.defer="is_filterable" class="checkbox checkbox-primary">
                    <span class="label-text">{{ __('backend.attributes.fields.is_filterable') }}</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" id="is_required" wire:model.defer="is_required" class="checkbox checkbox-primary">
                    <span class="label-text">{{ __('backend.attributes.fields.is_required') }}</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" wire:model.defer="is_active" class="checkbox checkbox-primary">
                    <span class="label-text">{{ __('backend.attributes.fields.is_active') }}</span>
                </label>
            </div>

            <x-ui.form-actions
                :submit-label="isset($attribute) ? __('backend.common.update') : __('backend.common.create')"
                :cancel-href="route('backend.attributes.index')"
                submit-target="save"
            />
        </form>
    </x-ui.card>
</x-backend.page>
