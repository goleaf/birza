@extends('layouts.backend.app')

@section('content')
<div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Buyer Information Card -->
    <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="md:flex">
                <!-- Left side with basic info -->
                <div class="p-8 flex-1">
                    <div class="flex items-center mb-6">
                        <div class="ml-6">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $buyer->name }}</h1>
                            <p class="text-gray-500">{{ $buyer->email }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers.fields.company') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->company_name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers.fields.company_code') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->company_code }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers.fields.vat_code') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->vat_code ?: '-' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">{{ __('buyers.fields.phone') }}</h3>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $buyer->phone ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right side with credit balance -->
                <div class="md:w-80 bg-gradient-to-br from-indigo-50 to-purple-50 p-8 flex flex-col justify-center border-t md:border-t-0 md:border-l border-gray-200">
                    <div class="text-center">
                        <h2 class="text-sm font-medium text-indigo-600 uppercase tracking-wide">{{ __('buyers.credit.current_balance') }}</h2>
                        <div class="mt-2 flex items-baseline justify-center">
                            <span class="text-4xl font-extrabold text-indigo-600">€{{ number_format($buyer->credit_balance, 2) }}</span>
                        </div>
                        <div class="mt-4">
                            <div class="inline-flex rounded-md shadow">
                                <a href="{{ route('backend.buyers.credit_history', $buyer) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('buyers.credit.view_history') }}
                                    <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Credit Management Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('buyers.credit.manage_credit') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('buyers.credit.manage_description') }}</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Credit Action Buttons -->
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" 
                                onclick="selectAction('add')"
                                id="addButton"
                                class="relative inline-flex items-center justify-center px-4 py-4 bg-green-600 text-white opacity-50">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                {{ __('buyers.credit_history.credit') }}
                            </span>
                        </button>

                        <button type="button"
                                onclick="selectAction('deduct')"
                                id="deductButton"
                                class="relative inline-flex items-center justify-center px-4 py-4 bg-red-600 text-white opacity-50">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                                {{ __('buyers.credit_history.debit') }}
                            </span>
                        </button>
                    </div>

                    <!-- Credit Form -->
                    <form id="creditForm" method="POST" class="space-y-6 hidden" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Amount Input -->
                        <div class="space-y-2">
                            <label for="amount" class="block text-sm font-medium text-gray-700">
                                {{ __('buyers.credit_history.amount') }}
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       step="0.01" 
                                       min="0.01"
                                       required
                                       class="block w-full pl-8 pr-12 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Note Input -->
                        <div class="space-y-2">
                            <label for="note" class="block text-sm font-medium text-gray-700">
                                {{ __('buyers.credit_history.note') }}
                            </label>
                            <textarea
                                name="note" 
                                id="note" 
                                rows="3"
                                class="block w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                placeholder="">{{ old('note') }}</textarea>
                        </div>

                        <!-- Attachment Input -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('common.attachment') }}
                            </label>
                            <input type="file" name="attachment" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                            <p class="mt-2 text-sm text-gray-500">{{ __('common.optional_upload_supporting_document') }}</p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                id="submitButton"
                                class="w-full flex items-center justify-center px-4 py-3 bg-indigo-600 text-white">
                            {{ __('buyers.credit_history.apply') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Credit History -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">{{ __('buyers.credit.view_history') }}</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers.credit_history.date') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers.credit_history.type') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers.credit_history.amount') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers.credit_history.note') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers.credit_history.balance_after') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('buyers.credit_history.admin') }}
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
                                            {{ __('buyers.credit_history.credit') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('buyers.credit_history.debit') }}
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
            </div>
        </div>
    </div>
</div>

<script>
    let selectedAction = null;
    const form = document.getElementById('creditForm');
    const addButton = document.getElementById('addButton');
    const deductButton = document.getElementById('deductButton');

    function selectAction(action) {
        selectedAction = action;
        form.classList.remove('hidden');
        
        // Reset both buttons
        addButton.className = 'relative inline-flex items-center justify-center px-4 py-4 bg-green-600 text-white opacity-50';
        deductButton.className = 'relative inline-flex items-center justify-center px-4 py-4 bg-red-600 text-white opacity-50';
        
        // Update selected button
        if (action === 'add') {
            addButton.className = 'relative inline-flex items-center justify-center px-4 py-4 bg-green-600 text-white';
            form.action = '{{ route('backend.buyers.add-credit', $buyer) }}';
            document.getElementById('submitButton').className = 'w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white';
        } else {
            deductButton.className = 'relative inline-flex items-center justify-center px-4 py-4 bg-red-600 text-white';
            form.action = '{{ route('backend.buyers.debit-credit', $buyer) }}';
            document.getElementById('submitButton').className = 'w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white';
        }
    }
</script>

@endsection
