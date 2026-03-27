<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">
            {{ __('backend_categories_title') }}
        </h2>
        <x-button
            primary
            :href="route('backend.categories.create')"
            :label="__('backend_categories_actions_create')"
        />
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="bg-white border-b border-gray-200">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ strtoupper(app()->getLocale()) }} {{ __('backend_categories_fields_name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('common_actions') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('backend_categories_fields_attributes') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($categories->whereNull('parent_category_id') as $category)
                        <tr class="bg-gray-200">
                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $category->getTranslation('category_name', app()->getLocale()) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                <a href="{{ route('backend.categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-900">
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($category->attributes->count() > 0)
                                    <div class="space-x-1">
                                        @foreach($category->attributes->sortBy(function($attribute) { return $attribute->getTranslation('name', app()->getLocale()); }) as $attribute)
                                            <span class="inline-block {{ $attribute->is_active ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }} px-2 py-1 rounded-full text-xs">
                                                {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    {{ __('common_no_attributes') }}
                                @endif
                            </td>
                        </tr>
                        @foreach ($category->subcategories as $subcategory)
                            <tr class="bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-gray-400 mr-2">└─</span>
                                        {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">
                                    <a href="{{ route('backend.categories.edit', $subcategory) }}" class="text-indigo-600 hover:text-indigo-900">
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($subcategory->attributes->count() > 0)
                                        <div class="space-x-1">
                                            @foreach($subcategory->attributes->sortBy(function($attribute) { return $attribute->getTranslation('name', app()->getLocale()); }) as $attribute)
                                                <span class="inline-block {{ $attribute->is_active ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }} px-2 py-1 rounded-full text-xs">
                                                    {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ __('common_no_attributes') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
