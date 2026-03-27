<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">{{ __('sellers_title') }}</h2>
        <x-button primary :href="route('backend.sellers.create')" :label="__('common_create')" />
    </div>

    <div class="bg-white shadow-sm rounded-lg mb-6 p-4">
        <form action="{{ route('backend.sellers.index') }}" method="GET" class="flex flex-wrap gap-4">
            <input
                type="text"
                name="search"
                id="search"
                value="{{ request('search') }}"
                placeholder="{{ __('common_search') }}"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >

            <select name="is_active" id="is_active" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('sellers_field_active_status_all') }}</option>
                <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>{{ __('sellers_field_active') }}</option>
                <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>{{ __('sellers_field_inactive') }}</option>
            </select>

            <select name="sort" id="sort" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common_newest') }}</option>
                <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common_oldest') }}</option>
                <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common_name_az') }}</option>
                <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common_name_za') }}</option>
                <option value="company_name,asc" {{ request('sort') === 'company_name,asc' ? 'selected' : '' }}>{{ __('common_company_az') }}</option>
                <option value="company_name,desc" {{ request('sort') === 'company_name,desc' ? 'selected' : '' }}>{{ __('common_company_za') }}</option>
            </select>

            <button type="submit" class="p-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>

            <a href="{{ route('backend.sellers.index') }}" class="p-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </a>
        </form>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers_field_name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers_field_email') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers_field_company_name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers_field_active_status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common_actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($sellers as $seller)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $seller->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $seller->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $seller->company_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $seller->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $seller->is_active ? __('sellers_field_active') : __('sellers_field_inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('backend.sellers.show', $seller) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('common_view') }}</a>
                            <a href="{{ route('backend.sellers.edit', $seller) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('common_edit') }}</a>
                            <a href="{{ route('backend.sellers.orders', $seller) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('sellers_orders_list') }}</a>
                            <x-button
                                xs
                                flat
                                negative
                                wire:click="confirmDeleteSeller({{ $seller->id }})"
                                :label="__('common_delete')"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $sellers->links() }}
    </div>
</div>
