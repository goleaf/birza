<x-backend.page :title="$buyer->name" :description="$buyer->email">
    <div class="space-y-6">
        <x-ui.card>
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center">
                <div class="flex-1">
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_company') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->company_name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_company_code') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->company_code }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_vat_code') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->vat_code ?: '-' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers_fields_phone') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->phone ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right side with credit balance -->
                <div class="md:w-80 bg-gradient-to-br from-indigo-50 to-purple-50 p-8 flex flex-col justify-center border-t md:border-t-0 md:border-l border-gray-200">
                    <div class="text-center">
                        <h2 class="text-sm font-medium text-indigo-600 uppercase tracking-wide">{{ __('buyers_credit_current_balance') }}</h2>
                        <div class="mt-2 flex items-baseline justify-center">
                            <span class="text-4xl font-extrabold text-indigo-600">€{{ number_format($buyer->credit_balance, 2) }}</span>
                        </div>
                        <div class="mt-4">
                            <div class="inline-flex rounded-md shadow">
                                <a href="{{ route('backend.buyers.credit_history', $buyer) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('buyers_credit_view_history') }}
                                    <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Credit Management Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('buyers_credit_manage_credit') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('buyers_credit_manage_description') }}</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Credit Action Buttons -->
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button"
                                wire:click="selectAction('add')"
                                id="addButton"
                                class="relative inline-flex items-center justify-center px-4 py-4 bg-green-600 text-white {{ $selectedAction === 'add' ? '' : 'opacity-50' }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                {{ __('buyers_credit_history_credit') }}
                            </span>
                        </button>

                        <button type="button"
                                wire:click="selectAction('deduct')"
                                id="deductButton"
                                class="relative inline-flex items-center justify-center px-4 py-4 bg-red-600 text-white {{ $selectedAction === 'deduct' ? '' : 'opacity-50' }}">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                                {{ __('buyers_credit_history_debit') }}
                            </span>
                        </button>
                    </div>

                    <form wire:submit.prevent="submitCredit" class="space-y-4 {{ $selectedAction ? '' : 'hidden' }}" enctype="multipart/form-data">
                        @error('action')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <!-- Amount Input -->
                        <div class="space-y-2">
                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                {{ __('buyers_credit_history_amount') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" 
                                       id="amount" 
                                       wire:model.defer="amount"
                                       step="0.01" 
                                       min="0.01"
                                       required
                                       class="block w-full pl-8 pr-12 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="0.00">
                            </div>
                            @error('amount')
                                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Note Input -->
                        <div class="space-y-2">
                            <label for="note" class="block text-sm font-medium text-gray-700">
                                {{ __('buyers_credit_history_note') }}
                            </label>
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

                        <!-- Attachment Input -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('common_attachment') }}
                            </label>
                            <input type="file" wire:model="attachment" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <p class="mt-2 text-sm text-gray-500">{{ __('common_optional_upload_supporting_document') }}</p>
                            @error('attachment')
                                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                id="submitButton"
                                wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center px-4 py-3 text-white disabled:opacity-60 disabled:cursor-not-allowed {{ $selectedAction === 'add' ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ __('buyers_credit_history_apply') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Credit History -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('buyers_credit_view_history') }}</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers_credit_history_date') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers_credit_history_type') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers_credit_history_amount') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers_credit_history_note') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers_credit_history_balance_after') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers_credit_history_admin') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($creditHistory as $history)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $history->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($history->type === 'add')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('buyers_credit_history_credit') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('buyers_credit_history_debit') }}
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
                                    {{ $history->admin->name }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-backend.page>
