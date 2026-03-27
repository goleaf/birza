<div>
<!-- start main container -->
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- start white container -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <!-- start content container -->
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- start header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">
                    {{ __('backend_attributes_title') }}
                </h2>
                <x-button
                    primary
                    :href="route('backend.attributes.create')"
                    :label="__('backend_attributes_actions_create')"
                />
            </div>
            <!-- end header -->

            <!-- start table container -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <!-- start table header -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend_attributes_fields_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend_attributes_fields_type') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend_attributes_fields_values_count') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend_attributes_fields_status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend_attributes_fields_is_filterable') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('backend_attributes_fields_is_required') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common_actions') }}</th>
                        </tr>
                    </thead>
                    <!-- end table header -->

                    <!-- start table body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($attributes as $attribute)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $attribute->getTranslation('name', app()->getLocale()) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ __('backend_attributes_types_' . $attribute->type) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('backend.attributes.values.index', $attribute) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $attribute->values_count ?? $attribute->values->count() }}
                                        {{ __('backend_attributes_fields_values') }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attribute->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $attribute->is_active ? __('common_active') : __('common_inactive') }}</span></td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attribute->is_filterable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $attribute->is_filterable ? __('common_yes') : __('common_no') }}</span></td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attribute->is_required ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $attribute->is_required ? __('common_yes') : __('common_no') }}</span></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('backend.attributes.values.create', $attribute) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('backend_attributes_actions_add_value') }}
                                    </a>
                                    <a href="{{ route('backend.attributes.edit', $attribute) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('common_edit') }}
                                    </a>
                                    <x-button
                                        xs
                                        flat
                                        negative
                                        wire:click="confirmDeleteAttribute({{ $attribute->id }})"
                                        :label="__('common_delete')"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <!-- end table body -->
                </table>
            </div>
            <!-- end table container -->

            <!-- start pagination -->
            <div class="mt-4">
                {{ $attributes->links() }}
            </div>
            <!-- end pagination -->
        </div>
        <!-- end content container -->
    </div>
    <!-- end white container -->
</div>
<!-- end main container -->
</div>
