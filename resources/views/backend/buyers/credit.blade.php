<x-backend.page :title="$buyer->name" :description="$buyer->email">
    <div class="space-y-6">
        <x-ui.card>
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center">
                <div class="flex-1">
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-sm text-gray-500">{{ __('buyers.fields.company') }}</div>
                            <div class="font-semibold">{{ $buyer->company_name }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('buyers.fields.company_code') }}</div>
                            <div class="font-semibold">{{ $buyer->company_code }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('buyers.fields.vat_code') }}</div>
                            <div class="font-semibold">{{ $buyer->vat_code ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('buyers.fields.phone') }}</div>
                            <div class="font-semibold">{{ $buyer->phone ?: '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-gray-50 p-6">
                    <div class="text-sm uppercase tracking-wide text-indigo-600">{{ __('buyers.credit.current_balance') }}</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-900">€{{ number_format($buyer->credit_balance, 2) }}</div>
                    <div class="mt-4">
                        <x-button
                            sm
                            primary
                            :href="route('backend.buyers.credit_history', $buyer)"
                            :label="__('buyers.credit.view_history')"
                        />
                    </div>
                </div>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-ui.card class="lg:col-span-1">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ __('buyers.credit.manage_credit') }}</h2>
                        <p class="text-sm text-gray-500">{{ __('buyers.credit.manage_description') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            wire:click="selectAction('add')"
                            id="addButton"
                            class="rounded-md border px-3 py-2 text-sm font-medium {{ $selectedAction === 'add' ? 'border-green-600 bg-green-50 text-green-700' : 'border-gray-300 text-gray-700' }}"
                        >
                            {{ __('buyers.credit_history.credit') }}
                        </button>
                        <button
                            type="button"
                            wire:click="selectAction('deduct')"
                            id="deductButton"
                            class="rounded-md border px-3 py-2 text-sm font-medium {{ $selectedAction === 'deduct' ? 'border-red-600 bg-red-50 text-red-700' : 'border-gray-300 text-gray-700' }}"
                        >
                            {{ __('buyers.credit_history.debit') }}
                        </button>
                    </div>

                    <form wire:submit.prevent="submitCredit" class="space-y-4 {{ $selectedAction ? '' : 'hidden' }}" enctype="multipart/form-data">
                        @error('action')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">{{ __('buyers.credit_history.amount') }}</label>
                            <input
                                type="number"
                                id="amount"
                                wire:model.defer="amount"
                                step="0.01"
                                min="0.01"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('amount') border-red-500 @enderror"
                                placeholder="0.00"
                            >
                            @error('amount')
                                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="note" class="block text-sm font-medium text-gray-700">{{ __('buyers.credit_history.note') }}</label>
                            <textarea
                                id="note"
                                wire:model.defer="note"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('note') border-red-500 @enderror"
                            ></textarea>
                            @error('note')
                                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('common.attachment') }}</label>
                            <input
                                type="file"
                                wire:model="attachment"
                                class="mt-1 block w-full rounded-md border-gray-300 @error('attachment') border-red-500 @enderror"
                            >
                            <span class="mt-2 block text-sm text-gray-500">{{ __('common.optional_upload_supporting_document') }}</span>
                            @error('attachment')
                                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            id="submitButton"
                            wire:loading.attr="disabled"
                            class="w-full rounded-md px-4 py-2 text-sm font-semibold text-white {{ $selectedAction === 'add' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}"
                        >
                            {{ __('buyers.credit_history.apply') }}
                        </button>
                    </form>
                </div>
            </x-ui.card>

            <x-ui.card class="lg:col-span-2">
                <h2 class="text-lg font-semibold">{{ __('buyers.credit.view_history') }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.credit_history.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.credit_history.type') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.credit_history.amount') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.credit_history.note') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.credit_history.balance_after') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyers.credit_history.admin') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($creditHistory as $history)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $history->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $history->type === 'add' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $history->type === 'add' ? __('buyers.credit_history.credit') : __('buyers.credit_history.debit') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm {{ $history->type === 'add' ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $history->type === 'add' ? '+' : '-' }}€{{ number_format(abs($history->amount), 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $history->note ?: '-' }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">€{{ number_format($history->balance_after, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $history->admin->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-backend.page>
