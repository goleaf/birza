<div>
    <div class="max-w-7xl mx-auto py-6 px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-4">{{ isset($category) ? __('backend_categories_edit_title') : __('backend_categories_create_title') }}</h2>

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

                    <div class="mb-4">
                        <label for="parent_category_id" class="block font-medium text-gray-700 mb-1">{{ __('backend_categories_fields_parent_category') }}</label>
                        <select id="parent_category_id" wire:model.defer="parent_category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('parent_category_id') border-red-500 @enderror">
                            <option value="">
                                {{ __('backend_categories_select_parent') }}
                            </option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->getTranslation('category_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_category_id')
                            <p class="mt-1 text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Translatable fields --}}
                    @foreach (config('app.locales') as $locale)
                        <div class="mb-4">
                            <label for="category_name_{{ $locale }}" class="block font-medium text-gray-700 mb-1">{{ strtoupper($locale) }} {{ __('backend_categories_fields_name') }}</label>
                            <input type="text" id="category_name_{{ $locale }}" wire:model.defer="name.{{ $locale }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name.' . $locale) border-red-500 @enderror" required>
                            @error('name.' . $locale)
                                <p class="mt-1 text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </x-backend.section>
                </div>

                    {{-- Attributes --}}
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-2">{{ __('backend_categories_fields_attributes') }}</label>

                        {{-- Notice for parent categories --}}
                        @if(!old('parent_category_id', isset($category) ? $category->parent_category_id : null))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                                <p>{{ __('backend_categories_attributes_reset_notice') }}</p>
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
                                        @if (!$attribute->is_active)
                                            <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">{{ __('backend_common_disabled') }}</span>
                                        @endif
                                        @if ($attribute->is_filterable)
                                            <span class="text-xs text-indigo-600">({{ __('backend_attributes_fields_is_filterable') }})</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('attributes')
                            <span class="mt-1 text-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('backend.categories.index') }}" class="mr-3 inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('backend_common_cancel') }}
                        </a>
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            {{ isset($category) ? __('backend_categories_actions_update') : __('backend_categories_actions_create') }}
                        </button>
                    </div>
                </form>
            </div>

            <x-ui.form-actions
                :submit-label="isset($category) ? __('backend.categories.actions.update') : __('backend.categories.actions.create')"
                :cancel-href="route('backend.categories.index')"
                submit-target="save"
            />
        </form>
    </x-ui.card>
</x-backend.page>
