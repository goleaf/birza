<div class="max-w-5xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold">
            {{ isset($category) ? __('backend_categories_edit_title') : __('backend_categories_create_title') }}
        </h2>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <form wire:submit.prevent="save" class="space-y-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div>
                        <label for="parent_category_id" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_categories_fields_parent_category') }}
                        </label>
                        <select
                            id="parent_category_id"
                            wire:model="parent_category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">{{ __('backend_categories_select_parent') }}</option>
                            @foreach ($parentCategories as $parentCategory)
                                <option value="{{ $parentCategory->id }}">
                                    {{ $parentCategory->getTranslation('category_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @foreach (config('app.locales') as $locale)
                        <div>
                            <label for="category_name_{{ $locale }}" class="mb-1 block text-sm font-medium text-gray-700">
                                {{ strtoupper($locale) }} {{ __('backend_categories_fields_name') }}
                            </label>
                            <input
                                type="text"
                                id="category_name_{{ $locale }}"
                                wire:model="name.{{ $locale }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                            @error('name.' . $locale)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700">{{ __('backend_categories_fields_attributes') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('backend_categories_attributes_reset_notice') }}</p>
                    </div>

                    <div class="max-h-96 space-y-3 overflow-y-auto rounded-md border border-gray-200 p-4">
                        @foreach ($availableAttributes->sortBy(fn ($attribute) => $attribute->getTranslation('name', app()->getLocale())) as $attribute)
                            <label class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    value="{{ $attribute->id }}"
                                    wire:model="selectedAttributes"
                                    class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                                <span class="text-sm text-gray-700">
                                    {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                    @if (! $attribute->is_active)
                                        <span class="ml-2 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                            {{ __('backend_common_disabled') }}
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedAttributes')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('selectedAttributes.*')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a
                    href="{{ route('backend.categories.index') }}"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    {{ __('backend_common_cancel') }}
                </a>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ isset($category) ? __('backend_categories_actions_update') : __('backend_categories_actions_create') }}
                </button>
            </div>
        </form>
    </div>
</div>
