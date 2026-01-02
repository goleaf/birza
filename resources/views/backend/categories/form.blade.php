@php
    $locales = config('app.locales');
    $activeLocale = in_array(app()->getLocale(), $locales, true) ? app()->getLocale() : ($locales[0] ?? 'en');
@endphp

<x-backend.page :title="isset($category) ? __('backend.categories.edit.title') : __('backend.categories.create.title')">
    <x-ui.card>
        <form wire:submit.prevent="save" class="space-y-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <x-backend.section :title="__('common.basic_information')">
                        <div class="space-y-6">
                            <div class="form-control">
                                <label for="parent_category_id" class="label">
                                    <span class="label-text">{{ __('backend.categories.fields.parent_category') }}</span>
                                </label>
                                <select
                                    id="parent_category_id"
                                    wire:model.defer="parent_category_id"
                                    class="select select-bordered w-full @error('parent_category_id') select-error @enderror"
                                >
                                    <option value="">{{ __('backend.categories.select_parent') }}</option>
                                    @foreach ($parentCategories as $cat)
                                        <option value="{{ $cat->id }}">
                                            {{ $cat->getTranslation('category_name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_category_id')
                                    <span class="mt-1 text-sm text-error">{{ $message }}</span>
                                @enderror
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
                                            <label for="category_name_{{ $locale }}" class="label">
                                                <span class="label-text">{{ strtoupper($locale) }} {{ __('backend.categories.fields.name') }}</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="category_name_{{ $locale }}"
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
                        </div>
                    </x-backend.section>
                </div>

                <div class="lg:col-span-1">
                    <x-backend.section :title="__('backend.categories.fields.attributes')">
                        @if (!old('parent_category_id', isset($category) ? $category->parent_category_id : null))
                            <div class="alert alert-warning">
                                <span>{{ __('backend.categories.attributes_reset_notice') }}</span>
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            @foreach ($attributes->sortBy(function ($attribute) { return $attribute->getTranslation('name', app()->getLocale()); }) as $attribute)
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        value="{{ $attribute->id }}"
                                        wire:model.defer="selectedAttributes"
                                        class="checkbox checkbox-primary mt-1"
                                    >
                                    <span class="text-sm">
                                        {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                        @if (! $attribute->is_active)
                                            <span class="badge badge-error badge-outline ml-2">
                                                {{ __('backend.common.disabled') }}
                                            </span>
                                        @endif
                                        @if ($attribute->is_filterable)
                                            <span class="ml-2 text-xs text-primary">({{ __('backend.attributes.fields.is_filterable') }})</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('attributes')
                            <span class="mt-1 text-sm text-error">{{ $message }}</span>
                        @enderror
                    </x-backend.section>
                </div>
            </div>

            <x-ui.form-actions
                :submit-label="isset($category) ? __('backend.categories.actions.update') : __('backend.categories.actions.create')"
                :cancel-href="route('backend.categories.index')"
                submit-target="save"
            />
        </form>
    </x-ui.card>
</x-backend.page>
