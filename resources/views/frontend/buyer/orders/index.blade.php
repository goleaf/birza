<!-- start extends -->
@extends('layouts.frontend.app')
<!-- end extends -->

<!-- start section -->
@section('content')
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <!-- start success message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <!-- end success message -->

        <!-- start errors -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- end errors -->

        <!-- start filters container -->
        <div class="bg-white shadow rounded-lg mb-6">
            <!-- start filters content -->
            <div class="p-4 sm:p-6">
                <!-- start form -->
                <form 
                    action="{{ route('buyer.orders.index') }}" 
                    method="GET"
                    class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4"
                >
                    <!-- start status select container -->
                    <div class="w-full sm:w-48">
                        <!-- start label -->
                        <label 
                            for="status"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('common.status') }}
                        </label>
                        <!-- end label -->
                        
                        <!-- start select -->
                        <select 
                            id="status" 
                            name="status"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                            <option value="">
                                {{ __('common.all') }}
                            </option>
                            @foreach ($orderStatuses as $key => $value)
                                <option 
                                    value="{{ $value }}" 
                                    {{ $filters['status'] === $value ? 'selected' : '' }}
                                >
                                    {{ __('orders.status_' . strtolower($key)) }}
                                </option>
                            @endforeach
                        </select>
                        <!-- end select -->
                    </div>
                    <!-- end status select container -->

                    <!-- start date from container -->
                    <div class="w-full sm:w-48">
                        <!-- start label -->
                        <label 
                            for="date_from"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('common.date_from') }}
                        </label>
                        <!-- end label -->
                        
                        <!-- start input -->
                        <input 
                            type="date" 
                            name="date_from" 
                            id="date_from" 
                            value="{{ $filters['dateFrom'] }}" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                        <!-- end input -->
                    </div>
                    <!-- end date from container -->

                    <!-- start date to container -->
                    <div class="w-full sm:w-48">
                        <!-- start label -->
                        <label 
                            for="date_to" 
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('common.date_to') }}
                        </label>
                        <!-- end label -->
                        
                        <!-- start input -->
                        <input 
                            type="date" 
                            name="date_to" 
                            id="date_to" 
                            value="{{ $filters['dateTo'] }}" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                        <!-- end input -->
                    </div>
                    <!-- end date to container -->

                    <!-- start button container -->
                    <div class="w-full sm:w-auto sm:self-end">
                        <!-- start label -->
                        <label 
                            for="filter_button" 
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            &nbsp;
                        </label>
                        <!-- end label -->
                        
                        <!-- start button -->
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium" 
                            name="filter_button"
                        >
                            {{ __('common.filter') }}
                        </button>
                        <!-- end button -->
                    </div>
                    <!-- end button container -->
                </form>
                <!-- end form -->
            </div>
            <!-- end filters content -->
        </div>
        <!-- end filters container -->

{{-- 
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('orders.total_orders') }}</h3>
                    <p class="text-2xl font-semibold text-gray-700">{{ $ordersData['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('orders.pending_orders') }}</h3>
                    <p class="text-2xl font-semibold text-gray-700">{{ $ordersData['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('orders.completed_orders') }}</h3>
                    <p class="text-2xl font-semibold text-gray-700">{{ $ordersData['delivered'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('orders.total_spent') }}</h3>
                    <p class="text-2xl font-semibold text-gray-700">€{{ number_format($ordersData['totalSpent'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>
--}}
        <!-- start orders list -->
        <div 
            class="bg-white shadow rounded-lg"
        >
            <!-- start overflow container -->
            <div 
                class="overflow-x-auto"
            >
                <!-- start table -->
                <table 
                    class="min-w-full divide-y divide-gray-200"
                >
                    <!-- start table head -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.order_number') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.date') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.total') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common.status') }}
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <!-- end table head -->

                    <!-- start table body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($ordersData['all'] as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($order->order_total, 2) }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $order->payment_status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->payment_status === 'shipped' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $order->payment_status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->payment_status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $order->payment_status === 'refunded' ? 'bg-purple-100 text-purple-800' : '' }}"
                                    >
                                        {{ __('orders.status_' . strtolower($order->payment_status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <!-- start view link -->
                                    <a 
                                        href="{{ route('buyer.orders.show', $order) }}"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        {{ __('common.view') }}
                                    </a>
                                    <!-- end view link -->

                                    @if ($order->payment_status === 'pending')
                                        <!-- start cancel form -->
                                        <form 
                                            action="{{ route('buyer.orders.cancel', $order) }}" 
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('PUT')
                                            
                                            <!-- start cancel button -->
                                            <button 
                                                type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('{{ __('orders.confirm_cancel') }}')"
                                            >
                                                {{ __('common.cancel') }}
                                            </button>
                                            <!-- end cancel button -->
                                        </form>
                                        <!-- end cancel form -->
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    {{ __('orders.no_orders_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!-- end table body -->
                </table>
                <!-- end table -->
            </div>
            <!-- end overflow container -->
        </div>
        <!-- end orders list -->
    </div>
@endsection
