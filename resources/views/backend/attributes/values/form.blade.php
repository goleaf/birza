@php
    $locales = config('app.locales');
    $activeLocale = in_array(app()->getLocale(), $locales, true) ? app()->getLocale() : ($locales[0] ?? 'en');
@endphp

<x-backend.page
    :title="(isset($attributeValue) ? __('backend.attribute_values.edit.title') : __('backend.attribute_values.create.title')) . ': ' . $attribute->getTranslation('name', app()->getLocale())"
>
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

                @foreach($locales as $locale)
                    <div x-show="locale === '{{ $locale }}'" x-cloak>
                        <div class="form-control">
                            <label for="value_{{ $locale }}" class="label">
                                <span class="label-text">{{ strtoupper($locale) }} {{ __('backend.attribute_values.fields.value') }}</span>
                            </label>
                            <input type="text" id="value_{{ $locale }}" wire:model.defer="value.{{ $locale }}"
                                class="input input-bordered w-full @error('value.' . $locale) input-error @enderror" required>
                            @error('value.' . $locale)
                                <span class="mt-1 text-sm text-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model.defer="is_active" class="checkbox checkbox-primary">
                <span class="label-text">{{ __('backend.attribute_values.fields.is_active') }}</span>
            </label>

            <x-ui.form-actions
                :submit-label="isset($attributeValue) ? __('backend.common.update') : __('backend.common.create')"
                :cancel-href="route('backend.attributes.values.index', $attribute)"
                submit-target="save"
            />
        </form>
    </x-ui.card>
</x-backend.page>
