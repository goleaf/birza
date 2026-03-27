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
    </x-slot:actions>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
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
                            <td>
                                @if ($category->attributes->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($category->attributes->sortBy(function($attribute) { return $attribute->getTranslation('name', app()->getLocale()); }) as $attribute)
                                            <span class="badge {{ $attribute->is_active ? 'badge-primary' : 'badge-error' }} badge-outline">
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
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="text-base-content/50">└─</span>
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
                                <td>
                                    @if ($subcategory->attributes->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($subcategory->attributes->sortBy(function($attribute) { return $attribute->getTranslation('name', app()->getLocale()); }) as $attribute)
                                                <span class="badge {{ $attribute->is_active ? 'badge-primary' : 'badge-error' }} badge-outline">
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
    </x-ui.card>
</x-backend.page>
