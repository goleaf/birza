<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">{{ __('sellers.title') }}</h2>
        <x-button primary :href="route('backend.sellers.create')" :label="__('common.create')" />
    </div>

    <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="p-4">
            <form action="{{ route('backend.sellers.index') }}" method="GET">
                <div class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="{{ __('common.search') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="w-48">
                        <select name="sort" id="sort" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common.newest') }}</option>
                            <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common.oldest') }}</option>
                            <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common.name_az') }}</option>
                            <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common.name_za') }}</option>
                            <option value="company_name,asc" {{ request('sort') === 'company_name,asc' ? 'selected' : '' }}>{{ __('common.company_az') }}</option>
                            <option value="company_name,desc" {{ request('sort') === 'company_name,desc' ? 'selected' : '' }}>{{ __('common.company_za') }}</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="p-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                        <a href="{{ route('backend.sellers.index') }}" class="p-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="bg-white border-b border-gray-200">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers.field_name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers.field_email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers.field_company_name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('sellers.field_stock') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($sellers as $seller)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $seller->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $seller->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $seller->company_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $seller->stock }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('backend.sellers.show', $seller) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('common.view') }}</a>
                                <a href="{{ route('backend.sellers.edit', $seller) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('common.edit') }}</a>
                                <x-button
                                    xs
                                    flat
                                    negative
                                    wire:click="confirmDeleteSeller({{ $seller->id }})"
                                    :label="__('common.delete')"
                                />
                                <a href="{{ route('backend.sellers.orders', $seller->id) }}" class="text-blue-600 hover:text-blue-900 ml-3">{{ __('sellers.orders_list') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $sellers->links() }}
    </div>
</div>
