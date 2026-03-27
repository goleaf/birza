<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ __('backend_attributes_title') }}</h1>
        </div>

        <x-button
            primary
            :href="route('backend.attributes.create')"
            :label="__('backend_attributes_actions_create')"
        />
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">{{ __('backend_attributes_stats_total') }}</div>
            <div class="mt-2 text-2xl font-semibold">{{ $stats['total'] }}</div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">{{ __('backend_attributes_stats_active') }}</div>
            <div class="mt-2 text-2xl font-semibold">{{ $stats['active'] }}</div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">{{ __('backend_attributes_stats_filterable') }}</div>
            <div class="mt-2 text-2xl font-semibold">{{ $stats['filterable'] }}</div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">{{ __('backend_attributes_stats_required') }}</div>
            <div class="mt-2 text-2xl font-semibold">{{ $stats['required'] }}</div>
        </div>
    </div>

    <div class="rounded-lg bg-white p-4 shadow-sm">
        <form action="{{ route('backend.attributes.index') }}" method="GET" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="{{ __('common_search') }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('backend_attributes_filters_all_statuses') }}</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>{{ __('common_active') }}</option>
                    <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>{{ __('common_inactive') }}</option>
                </select>
            </div>

            <div>
                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('backend_attributes_filters_all_types') }}</option>
                    @foreach ($types as $typeKey => $typeLabel)
                        <option value="{{ $typeKey }}" {{ $filters['type'] === $typeKey ? 'selected' : '' }}>
                            {{ __('backend_attributes_types_' . $typeKey) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    {{ __('common_filter') }}
                </button>
                <a href="{{ route('backend.attributes.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                    {{ __('common_reset') }}
                </a>
            </div>

            <div>
                <select name="filterable" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('backend_attributes_fields_is_filterable') }}</option>
                    <option value="1" {{ $filters['filterable'] === '1' ? 'selected' : '' }}>{{ __('common_yes') }}</option>
                    <option value="0" {{ $filters['filterable'] === '0' ? 'selected' : '' }}>{{ __('common_no') }}</option>
                </select>
            </div>

            <div>
                <select name="required" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('backend_attributes_fields_is_required') }}</option>
                    <option value="1" {{ $filters['required'] === '1' ? 'selected' : '' }}>{{ __('common_yes') }}</option>
                    <option value="0" {{ $filters['required'] === '0' ? 'selected' : '' }}>{{ __('common_no') }}</option>
                </select>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_attributes_fields_name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_attributes_fields_type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_attributes_fields_values_count') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_attributes_fields_status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_attributes_fields_is_filterable') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_attributes_fields_is_required') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($attributes as $attribute)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $attribute->getTranslation('name', app()->getLocale()) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ __('backend_attributes_types_' . $attribute->type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('backend.attributes.values.index', $attribute) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $attribute->values_count ?? $attribute->values->count() }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $attribute->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $attribute->is_active ? __('common_active') : __('common_inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $attribute->is_filterable ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $attribute->is_filterable ? __('common_yes') : __('common_no') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $attribute->is_required ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $attribute->is_required ? __('common_yes') : __('common_no') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                {{ __('common_no_results') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $attributes->links() }}
        </div>
    </div>
</div>
