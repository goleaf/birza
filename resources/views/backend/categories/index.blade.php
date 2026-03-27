<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">{{ __('backend_categories_title') }}</h2>
        <x-button
            primary
            :href="route('backend.categories.create')"
            :label="__('backend_categories_actions_create')"
        />
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ strtoupper(app()->getLocale()) }} {{ __('backend_categories_fields_name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('backend_categories_fields_attributes') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('common_actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($categories as $category)
                        <tr class="bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                {{ $category->getTranslation('category_name', app()->getLocale()) }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($category->attributes->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($category->attributes->sortBy(fn ($attribute) => $attribute->getTranslation('name', app()->getLocale())) as $attribute)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $attribute->is_active ? 'bg-indigo-100 text-indigo-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">{{ __('common_no_attributes') }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <a href="{{ route('backend.categories.edit', $category) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">
                                    {{ __('common_edit') }}
                                </a>
                                <x-button
                                    xs
                                    flat
                                    negative
                                    wire:click="confirmDeleteCategory({{ $category->id }})"
                                    :label="__('common_delete')"
                                />
                            </td>
                        </tr>

                        @foreach ($category->subcategories as $subcategory)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-gray-700">
                                        <span class="text-gray-400">└─</span>
                                        <span>{{ $subcategory->getTranslation('category_name', app()->getLocale()) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($subcategory->attributes->isNotEmpty())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($subcategory->attributes->sortBy(fn ($attribute) => $attribute->getTranslation('name', app()->getLocale())) as $attribute)
                                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $attribute->is_active ? 'bg-indigo-100 text-indigo-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">{{ __('common_no_attributes') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    <a href="{{ route('backend.categories.edit', $subcategory) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">
                                        {{ __('common_edit') }}
                                    </a>
                                    <x-button
                                        xs
                                        flat
                                        negative
                                        wire:click="confirmDeleteCategory({{ $subcategory->id }})"
                                        :label="__('common_delete')"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
