<div>
<!-- start main container -->
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- start header -->
    <div class="px-4 sm:px-0 mb-4 flex justify-between items-center">
        <h2 class="text-2xl font-bold">
            {{ __('backend_attribute_values_index_title') }}: {{ $attribute->getTranslation('name', app()->getLocale()) }}
        </h2>
        <x-button
            primary
            :href="route('backend.attributes.values.create', $attribute)"
            :label="__('backend_attribute_values_actions_create')"
        />
    </div>
    <!-- end header -->

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ strtoupper(app()->getLocale()) }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend_attribute_values_fields_status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($values as $value)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $value->getTranslation('value', app()->getLocale()) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $value->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $value->is_active ? __('backend_common_active') : __('backend_common_inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('backend.attributes.values.edit', [$attribute, $value]) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    {{ __('common_edit') }}
                                </a>
                                <x-button
                                    xs
                                    flat
                                    negative
                                    wire:click="confirmDeleteValue({{ $value->id }})"
                                    :label="__('common_delete')"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-backend.page>
