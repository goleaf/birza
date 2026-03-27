<div>
<div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Buyer Information Card -->
    <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-8">
                <div class="flex items-center mb-6">
                    <div class="ml-6">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $buyer->name }}</h1>
                        <div class="mt-1">
                            <span class="text-2xl font-bold text-indigo-600">€{{ number_format($buyer->credit_balance, 2) }}</span>
                            <span class="ml-2 text-sm text-gray-500">{{ __('backend_buyers_credit_current_balance') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form method="GET" class="flex items-center space-x-4">
                <!-- Type Filter -->
                <div class="flex-1">
                    <label for="type" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_credit_history_filter_type') }}</label>
                    <select name="type" id="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">{{ __('backend_buyers_credit_history_filter_all_types') }}</option>
                        <option value="add" {{ request('type') === 'add' ? 'selected' : '' }}>{{ __('backend_buyers_credit_history_table_credit') }}</option>
                        <option value="deduct" {{ request('type') === 'deduct' ? 'selected' : '' }}>{{ __('backend_buyers_credit_history_table_debit') }}</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="flex-1">
                    <label for="date_from" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_credit_history_filter_date_from') }}</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Date To -->
                <div class="flex-1">
                    <label for="date_to" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_credit_history_filter_date_to') }}</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        {{ __('backend_buyers_credit_history_filter_apply') }}
                    </button>

                    <button type="button"
                            wire:click="exportCsv"
                            class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('backend_common_export_csv') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Credit History Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('backend_buyers_credit_history_table_date') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('backend_buyers_credit_history_table_type') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('backend_buyers_credit_history_table_amount') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('backend_buyers_credit_history_table_note') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('backend_buyers_credit_history_table_balance_after') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('backend_buyers_credit_history_table_admin') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($creditHistory as $history)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $history->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($history->type === 'add')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('backend_buyers_credit_history_table_credit') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('backend_buyers_credit_history_table_debit') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $history->type === 'add' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $history->type === 'add' ? '+' : '-' }}€{{ number_format(abs($history->amount), 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                            {{ $history->note ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                            €{{ number_format($history->balance_after, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $history->admin ? $history->admin->name : __('backend_buyers_credit_history_table_system') }}
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td colspan="6" class="px-6 py-4">
                            @if($history->attachments->isNotEmpty())
                                <div class="mt-2 text-sm">
                                    <span class="text-gray-600">{{ __('common_attachments') }}:</span>
                                    @foreach($history->attachments as $attachment)
                                        <button type="button"
                                                wire:click="downloadAttachment({{ $attachment->id }})"
                                                class="ml-2 text-primary-600 hover:text-primary-800">
                                            {{ $attachment->original_name }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            {{ __('backend_buyers_credit_history_table_no_records') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $creditHistory->withQueryString()->links() }}
    </div>
</div>
</div>
