<div>
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">{{ __('buyers.title') }}</h2>
    <x-button primary :href="route('backend.buyers.create')" :label="__('common.create')" />
  </div>

  <div class="bg-white shadow-sm rounded-lg mb-6 p-4">
    <form action="{{ route('backend.buyers.index') }}" method="GET" class="flex flex-wrap gap-4">
      <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('common.search') }}" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      <input type="number" name="min_balance" id="min_balance" value="{{ request('min_balance') }}" placeholder="{{ __('common.min_balance') }}" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      <input type="number" name="max_balance" id="max_balance" value="{{ request('max_balance') }}" placeholder="{{ __('common.max_balance') }}" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
      <select name="is_verified" id="is_verified" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('buyers.field_verification_status_all') }}</option>
        <option value="true" {{ request('is_verified') === 'true' ? 'selected' : '' }}>{{ __('buyers.field_verified') }}</option>
        <option value="false" {{ request('is_verified') === 'false' ? 'selected' : '' }}>{{ __('buyers.field_not_verified') }}</option>
      </select>
      <select name="is_active" id="is_active" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">{{ __('buyers.field_active_status_all') }}</option>
        <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>{{ __('buyers.field_active') }}</option>
        <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>{{ __('buyers.field_inactive') }}</option>
      </select>
      <select name="sort" id="sort" class="w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common.newest') }}</option>
        <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common.oldest') }}</option>
        <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common.name_az') }}</option>
        <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common.name_za') }}</option>
        <option value="company_name,asc" {{ request('sort') === 'company_name,asc' ? 'selected' : '' }}>{{ __('common.company_az') }}</option>
        <option value="company_name,desc" {{ request('sort') === 'company_name,desc' ? 'selected' : '' }}>{{ __('common.company_za') }}</option>
      </select>
      <button type="submit" class="p-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
      </button>
      <a href="{{ route('backend.buyers.index') }}" class="p-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
      </a>
    </form>
  </div>

  <div class="bg-white shadow-sm sm:rounded-lg">
    <table class="w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('buyers.field_name') }}</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('buyers.field_email') }}</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('buyers.field_company_name') }}</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('buyers.field_credit_balance') }}</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('buyers.field_verification_status') }}</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('buyers.field_active_status') }}</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($buyers as $buyer)
          <tr>
            <td class="px-6 py-4 whitespace-nowrap">{{ $buyer->name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $buyer->email }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $buyer->company_name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $buyer->credit_balance }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $buyer->is_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $buyer->is_verified ? __('buyers.field_verified') : __('buyers.field_not_verified') }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $buyer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $buyer->is_active ? __('buyers.field_active') : __('buyers.field_inactive') }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <a href="{{ route('backend.buyers.credit', $buyer) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('common.balance') }}</a>

              <a href="{{ route('backend.buyers.orders', $buyer) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('common.orders') }}</a>
              <a href="{{ route('backend.buyers.edit', $buyer) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('common.edit') }}</a>
              <x-button
                  xs
                  flat
                  negative
                  wire:click="confirmDeleteBuyer({{ $buyer->id }})"
                  :label="__('common.delete')"
              />
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $buyers->links() }}
  </div>
</div>
