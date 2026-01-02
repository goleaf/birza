<x-backend.page :title="__('backend.categories.title')">
    <x-slot:actions>
        <x-ui.button
            primary
            :href="route('backend.categories.create')"
            :label="__('backend.categories.actions.create')"
        />
    </x-slot:actions>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>{{ strtoupper(app()->getLocale()) }} {{ __('backend.categories.fields.name') }}</th>
                        <th>{{ __('common.actions') }}</th>
                        <th>{{ __('backend.categories.fields.attributes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr class="bg-base-200">
                            <td class="font-semibold">{{ $category->getTranslation('category_name', app()->getLocale()) }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <x-ui.button
                                        xs
                                        flat
                                        :href="route('backend.categories.edit', $category)"
                                        :label="__('common.edit')"
                                    />
                                    <x-ui.button
                                        xs
                                        flat
                                        negative
                                        type="button"
                                        wire:click="confirmDeleteCategory({{ $category->id }})"
                                        :label="__('common.delete')"
                                    />
                                </div>
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
                                    {{ __('common.no_attributes') }}
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
                                <td>
                                    <div class="flex items-center gap-2">
                                        <x-ui.button
                                            xs
                                            flat
                                            :href="route('backend.categories.edit', $subcategory)"
                                            :label="__('common.edit')"
                                        />
                                        <x-ui.button
                                            xs
                                            flat
                                            negative
                                            type="button"
                                            wire:click="confirmDeleteCategory({{ $subcategory->id }})"
                                            :label="__('common.delete')"
                                        />
                                    </div>
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
                                        {{ __('common.no_attributes') }}
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
