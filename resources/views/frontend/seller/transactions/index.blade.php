@extends('layouts.frontend.app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- start stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- current balance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 truncate">{{ __('transactions.current_balance') }}</h3>
                <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['current_balance'], 2) }} €</p>
            </div>

            <!-- total deductions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 truncate">{{ __('transactions.total_deductions') }}</h3>
                <p class="mt-1 text-3xl font-semibold text-red-600">{{ number_format($stats['total_deductions'], 2) }} €</p>
            </div>

            <!-- total refunds -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 truncate">{{ __('transactions.total_refunds') }}</h3>
                <p class="mt-1 text-3xl font-semibold text-green-600">{{ number_format($stats['total_refunds'], 2) }} €</p>
            </div>
        </div>
        <!-- end stats -->

        <!-- start filters -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
                    <!-- type filter -->
                    <div class="w-full sm:w-48">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('transactions.type') }}
                        </label>
                        <select 
                            id="type" 
                            name="type"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                            <option value="">{{ __('common.all') }}</option>
                            <option value="deduction" {{ $filters['type'] === 'deduction' ? 'selected' : '' }}>
                                {{ __('transactions.type_deduction') }}
                            </option>
                            <option value="refund" {{ $filters['type'] === 'refund' ? 'selected' : '' }}>
                                {{ __('transactions.type_refund') }}
                            </option>
                        </select>
                    </div>

                    <!-- date from filter -->
                    <div class="w-full sm:w-48">
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('common.date_from') }}
                        </label>
                        <input 
                            type="date" 
                            name="date_from" 
                            id="date_from" 
                            value="{{ $filters['dateFrom'] }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                    </div>

                    <!-- date to filter -->
                    <div class="w-full sm:w-48">
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('common.date_to') }}
                        </label>
                        <input 
                            type="date" 
                            name="date_to" 
                            id="date_to" 
                            value="{{ $filters['dateTo'] }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                    </div>

                    <!-- filter button -->
                    <div class="w-full sm:w-auto sm:self-end">
                        <button 
                            type="submit"
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                        >
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- end filters -->

        <!-- start transactions list -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('transactions.date') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('transactions.order') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('transactions.type') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('transactions.description') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('transactions.amount') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <a 
                                            href="{{ route('seller.orders.show', $transaction->order_id) }}" 
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            #{{ $transaction->order_id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $transaction->type === 'deduction' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}"
                                        >
                                            {{ __('transactions.type_' . $transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium
                                        {{ $transaction->type === 'deduction' ? 'text-red-600' : 'text-green-600' }}"
                                    >
                                        {{ number_format($transaction->amount, 2) }} €
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        {{ __('transactions.no_transactions') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- start pagination -->
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
                <!-- end pagination -->
            </div>
        </div>
        <!-- end transactions list -->
    </div>
@endsection
