<x-backend.page :title="__('buyers.title')">
    <x-slot:actions>
        <x-button
            primary
            :href="route('backend.buyers.create')"
            :label="__('common.create')"
        />
    </x-slot:actions>

    <div class="space-y-6">
        <x-ui.card>
            <form action="{{ route('backend.buyers.index') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">{{ __('common.search') }}</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="min_balance" class="block text-sm font-medium text-gray-700">{{ __('common.min_balance') }}</label>
                    <input type="number" name="min_balance" id="min_balance" value="{{ request('min_balance') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="max_balance" class="block text-sm font-medium text-gray-700">{{ __('common.max_balance') }}</label>
                    <input type="number" name="max_balance" id="max_balance" value="{{ request('max_balance') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="is_verified" class="block text-sm font-medium text-gray-700">{{ __('buyers.field_verification_status_all') }}</label>
                    <select name="is_verified" id="is_verified" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('buyers.field_verification_status_all') }}</option>
                        <option value="true" {{ request('is_verified') === 'true' ? 'selected' : '' }}>{{ __('buyers.field_verified') }}</option>
                        <option value="false" {{ request('is_verified') === 'false' ? 'selected' : '' }}>{{ __('buyers.field_not_verified') }}</option>
                    </select>
                </div>
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">{{ __('buyers.field_active_status_all') }}</label>
                    <select name="is_active" id="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('buyers.field_active_status_all') }}</option>
                        <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>{{ __('buyers.field_active') }}</option>
                        <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>{{ __('buyers.field_inactive') }}</option>
                    </select>
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700">{{ __('common.sort_by') }}</label>
                    <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common.newest') }}</option>
                        <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common.oldest') }}</option>
                        <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common.name_az') }}</option>
                        <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common.name_za') }}</option>
                        <option value="company_name,asc" {{ request('sort') === 'company_name,asc' ? 'selected' : '' }}>{{ __('common.company_az') }}</option>
                        <option value="company_name,desc" {{ request('sort') === 'company_name,desc' ? 'selected' : '' }}>{{ __('common.company_za') }}</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 md:col-span-3">
                    <x-button type="submit" primary :label="__('common.filter')" />
                    <x-button flat :href="route('backend.buyers.index')" :label="__('common.reset')" />
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.field_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.field_email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.field_company_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.field_credit_balance') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.field_verification_status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.field_active_status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($buyers as $buyer)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $buyer->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $buyer->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $buyer->company_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $buyer->credit_balance }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $buyer->is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $buyer->is_verified ? __('buyers.field_verified') : __('buyers.field_not_verified') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $buyer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $buyer->is_active ? __('buyers.field_active') : __('buyers.field_inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-button xs flat :href="route('backend.buyers.credit', $buyer)" :label="__('common.balance')" />
                                        <x-button xs flat :href="route('backend.buyers.orders', $buyer)" :label="__('common.orders')" />
                                        <x-button xs flat :href="route('backend.buyers.edit', $buyer)" :label="__('common.edit')" />
                                        <x-button xs flat negative wire:click="confirmDeleteBuyer({{ $buyer->id }})" :label="__('common.delete')" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <div>
            {{ $buyers->links() }}
        </div>
    </div>
</x-backend.page>
