<div>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold">{{ __('buyers_title') }}</h2>
        <x-button primary :href="route('backend.buyers.create')" :label="__('common_create')" />
    </div>

    <div class="mb-6 rounded-lg bg-white p-4 shadow-sm">
        <form action="{{ route('backend.buyers.index') }}" method="GET" class="flex flex-wrap gap-4">
            <input
                type="text"
                name="search"
                id="search"
                value="{{ request('search') }}"
                placeholder="{{ __('common_search') }}"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            <input
                type="number"
                name="min_balance"
                id="min_balance"
                value="{{ request('min_balance') }}"
                placeholder="{{ __('common_min_balance') }}"
                class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            <input
                type="number"
                name="max_balance"
                id="max_balance"
                value="{{ request('max_balance') }}"
                placeholder="{{ __('common_max_balance') }}"
                class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
            <select name="is_verified" id="is_verified" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('buyers_field_verification_status_all') }}</option>
                <option value="true" {{ request('is_verified') === 'true' ? 'selected' : '' }}>{{ __('buyers_field_verified') }}</option>
                <option value="false" {{ request('is_verified') === 'false' ? 'selected' : '' }}>{{ __('buyers_field_not_verified') }}</option>
            </select>
            <select name="is_active" id="is_active" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">{{ __('buyers_field_active_status_all') }}</option>
                <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>{{ __('buyers_field_active') }}</option>
                <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>{{ __('buyers_field_inactive') }}</option>
            </select>
            <select name="sort" id="sort" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common_newest') }}</option>
                <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common_oldest') }}</option>
                <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common_name_az') }}</option>
                <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common_name_za') }}</option>
                <option value="company_name,asc" {{ request('sort') === 'company_name,asc' ? 'selected' : '' }}>{{ __('common_company_az') }}</option>
                <option value="company_name,desc" {{ request('sort') === 'company_name,desc' ? 'selected' : '' }}>{{ __('common_company_za') }}</option>
            </select>
            <button type="submit" class="rounded-md bg-indigo-600 p-2 text-white hover:bg-indigo-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
            <a href="{{ route('backend.buyers.index') }}" class="rounded-md bg-gray-500 p-2 text-white hover:bg-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </a>
        </form>
    </div>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers_field_name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers_field_email') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers_field_company_name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers_field_credit_balance') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers_field_verification_status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers_field_active_status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('common_actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach ($buyers as $buyer)
                    <tr>
                        <td class="whitespace-nowrap px-6 py-4">{{ $buyer->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4">{{ $buyer->email }}</td>
                        <td class="whitespace-nowrap px-6 py-4">{{ $buyer->company_name }}</td>
                        <td class="whitespace-nowrap px-6 py-4">{{ $buyer->credit_balance }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $buyer->is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $buyer->is_verified ? __('buyers_field_verified') : __('buyers_field_not_verified') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $buyer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $buyer->is_active ? __('buyers_field_active') : __('buyers_field_inactive') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                            <a href="{{ route('backend.buyers.credit', $buyer) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">{{ __('common_balance') }}</a>
                            <a href="{{ route('backend.buyers.orders', $buyer) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">{{ __('common_orders') }}</a>
                            <a href="{{ route('backend.buyers.edit', $buyer) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">{{ __('common_edit') }}</a>
                            <x-button
                                xs
                                flat
                                negative
                                wire:click="confirmDeleteBuyer({{ $buyer->id }})"
                                :label="__('common_delete')"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $buyers->links() }}
    </div>
</div>
