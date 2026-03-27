<div class="space-y-6">
    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="p-8">
            <h1 class="text-2xl font-bold text-gray-900">{{ $buyer->name }}</h1>
            <div class="mt-2">
                <span class="text-2xl font-bold text-indigo-600">€{{ number_format($buyer->credit_balance, 2) }}</span>
                <span class="ml-2 text-sm text-gray-500">{{ __('backend_buyers_credit_current_balance') }}</span>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow-sm">
        <form method="GET" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_credit_history_filter_type') }}</label>
                <select
                    name="type"
                    id="type"
                    class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="">{{ __('backend_buyers_credit_history_filter_all_types') }}</option>
                    <option value="add" {{ request('type') === 'add' ? 'selected' : '' }}>{{ __('backend_buyers_credit_history_table_credit') }}</option>
                    <option value="deduct" {{ request('type') === 'deduct' ? 'selected' : '' }}>{{ __('backend_buyers_credit_history_table_debit') }}</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_credit_history_filter_date_from') }}</label>
                <input
                    type="date"
                    name="date_from"
                    id="date_from"
                    value="{{ request('date_from') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700">{{ __('backend_buyers_credit_history_filter_date_to') }}</label>
                <input
                    type="date"
                    name="date_to"
                    id="date_to"
                    value="{{ request('date_to') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
            </div>

            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    {{ __('backend_buyers_credit_history_filter_apply') }}
                </button>

                <button
                    type="button"
                    wire:click="exportCsv"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    {{ __('backend_common_export_csv') }}
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_buyers_credit_history_table_date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_buyers_credit_history_table_type') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_buyers_credit_history_table_amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_buyers_credit_history_table_note') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_buyers_credit_history_table_balance_after') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend_buyers_credit_history_table_admin') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($creditHistory as $history)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $history->created_at->format('Y-m-d H:i') }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($history->type === 'add')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ __('backend_buyers_credit_history_table_credit') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        {{ __('backend_buyers_credit_history_table_debit') }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm {{ $history->type === 'add' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $history->type === 'add' ? '+' : '-' }}€{{ number_format(abs($history->amount), 2) }}
                            </td>
                            <td class="max-w-xs px-6 py-4 text-sm text-gray-500">{{ $history->note ?: '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-gray-900">€{{ number_format($history->balance_after, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ $history->admin ? $history->admin->name : __('backend_buyers_credit_history_table_system') }}
                            </td>
                        </tr>
                        @if ($history->attachments->isNotEmpty())
                            <tr>
                                <td colspan="6" class="bg-gray-50 px-6 py-4">
                                    <div class="text-sm">
                                        <span class="text-gray-600">{{ __('common_attachments') }}:</span>
                                        @foreach ($history->attachments as $attachment)
                                            <button
                                                type="button"
                                                wire:click="downloadAttachment({{ $attachment->id }})"
                                                class="ml-2 text-indigo-600 hover:text-indigo-900"
                                            >
                                                {{ $attachment->original_name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
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
    </div>

    <div>
        {{ $creditHistory->withQueryString()->links() }}
    </div>
</div>
