<!-- start extends -->
@extends('layouts.frontend.app')
<!-- end extends -->

<!-- start section -->
@section('content')
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">{{ __('common.error_occurred') }}</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <!-- start order header -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
            <!-- start header top bar -->
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a 
                            href="{{ route('seller.orders.index') }}"
                            class="inline-flex items-center text-gray-500 hover:text-gray-700"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="ml-1 text-sm font-medium">{{ __('common.back_to_orders') }}</span>
                        </a>
                        <div class="h-6 w-px bg-gray-300"></div>
                        <h1 class="text-lg font-semibold text-gray-900">
                            {{ __('orders.order_details') }} #{{ $order->id }}
                        </h1>
                    </div>
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if ($order->payment_status === \App\Models\Order::STATUS['PENDING']) bg-yellow-100 text-yellow-800
                        @elseif($order->payment_status === \App\Models\Order::STATUS['PAID']) bg-green-100 text-green-800
                        @elseif($order->payment_status === \App\Models\Order::STATUS['CANCELLED']) bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif"
                    >
                        {{ __('orders.status_' . strtolower($order->payment_status)) }}
                    </div>
                </div>
            </div>
            <!-- end header top bar -->

            <!-- start order info -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders.placed_on') }}</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders.buyer') }}</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->buyer->name }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders.total_amount') }}</h3>
                        <p class="mt-1 text-sm text-gray-900">{{ number_format($order->total, 2) }} €</p>
                    </div>
                </div>
            </div>
            <!-- end order info -->

            <!-- start status update form -->
            <div class="px-6 py-4">
                @if($order->payment_status === \App\Models\Order::STATUS['PENDING'])
                    <div class="flex space-x-4">
                        <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS['PAID'] }}">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ __('orders.confirm_order') }}
                            </button>
                        </form>

                        <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ \App\Models\Order::STATUS['CANCELLED'] }}">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ __('orders.cancel_order') }}
                            </button>
                        </form>
                    </div>

                    <div class="mt-4">
                        <label for="comment" class="block text-sm font-medium text-gray-700">{{ __('orders.comment') }}</label>
                        <div class="mt-1">
                            <textarea
                                id="comment"
                                name="comment"
                                rows="3"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                placeholder="{{ __('orders.comment_placeholder') }}"
                            >{{ old('comment') }}</textarea>
                        </div>
                    </div>
                @else
                    <div class="text-sm text-gray-500">
                        {{ __('orders.status_cannot_be_changed') }}
                    </div>
                @endif
            </div>
            <!-- end status update form -->
            </div>
            <!-- end order status -->
        </div>
        <!-- end order header -->

        <!-- start order items -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
            <!-- start items header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ __('orders.order_items') }}
                </h2>
            </div>
            <!-- end items header -->

            <!-- start table container -->
            <div class="overflow-x-auto">
                <!-- start table -->
                <table class="min-w-full divide-y divide-gray-200">
                    <!-- start table header -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.product') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.quantity') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.unit_price') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.total') }}
                            </th>
                        </tr>
                    </thead>
                    <!-- end table header -->

                    <!-- start table body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($orderItems as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if ($item->product->product_image)
                                                <img 
                                                    src="{{ Storage::url('products/' . $item->product->product_image) }}"
                                                    class="h-10 w-10 rounded-full object-cover"
                                                >
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->product->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($item->unit_price, 2) }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($item->total_price, 2) }} €
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <!-- end table body -->

                    <!-- start table footer -->
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                {{ __('orders.order_total') }}:
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ number_format($order->order_total, 2) }} €<br>
                                <span class="text-gray-500 font-normal">({{ __('product.price_without_vat') }})</span>
                            </td>
                        </tr>
                    </tfoot>
                    <!-- end table footer -->
                </table>
                <!-- end table -->
            </div>
            <!-- end table container -->
        </div>
        <!-- end order items -->

    </div>
    <!-- end main container -->
@endsection
<!-- end section -->
