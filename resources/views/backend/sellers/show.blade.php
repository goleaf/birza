@extends('layouts.backend.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Seller Information -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-900">{{ $seller->company_name }}</h1>
                <div class="flex space-x-3">
                    <a href="{{ route('backend.sellers.edit', $seller) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('common.edit') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('sellers.contact_person') }}</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $seller->name }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('sellers.email') }}</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $seller->email }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('sellers.vat_code') }}</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $seller->vat_code ?: '-' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">{{ __('sellers.phone') }}</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $seller->phone ?: '-' }}</p>
                </div>
                <div class="md:col-span-2">
                    <h3 class="text-sm font-medium text-gray-500">{{ __('sellers.address') }}</h3>
                    <p class="mt-1 text-lg font-medium text-gray-900">{{ $seller->address ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('sellers.products') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('products.name') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('products.price') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('products.times_ordered') }}
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('products.status') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             alt="{{ $product->name }}"
                                             class="h-10 w-10 rounded-full object-cover">
                                    @endif
                                    <div class="ml-4">
                                        <a href="{{ route('backend.products.show', $product) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ $product->name }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                €{{ number_format($product->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                {{ $product->order_items_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($product->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('common.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ __('common.inactive') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ __('products.no_products') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Orders -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('sellers.orders') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                            {{ __('orders.id') }}
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                            {{ __('orders.buyer') }}
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 w-1/3">
                            {{ __('orders.items') }}
                        </th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">
                            {{ __('orders.total') }}
                        </th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600">
                            {{ __('orders.status') }}
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                            {{ __('orders.date') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('backend.orders.show', $order) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $order->id }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    @if($order->buyer)
                                        {{ $order->buyer->company_name }}
                                    @else
                                        <span class="text-gray-500 italic">{{ __('orders.buyer_not_found') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    @foreach($order->orderItems as $item)
                                        <div class="flex justify-between text-sm">
                                            <div class="font-medium text-gray-900">
                                                {{ $item->product->name }}
                                                <span class="text-gray-500 ml-1">×{{ $item->quantity }}</span>
                                            </div>
                                            <div class="text-gray-900">
                                                €{{ number_format($item->total_price, 2) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">
                                €{{ number_format($order->order_total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusClass = match($order->payment_status) {
                                        $orderStatuses['PENDING'] => 'bg-yellow-100 text-yellow-800',
                                        $orderStatuses['PAID'] => 'bg-green-100 text-green-800',
                                        $orderStatuses['PROCESSING'] => 'bg-blue-100 text-blue-800',
                                        $orderStatuses['SHIPPED'] => 'bg-purple-100 text-purple-800',
                                        $orderStatuses['DELIVERED'] => 'bg-green-100 text-green-800',
                                        $orderStatuses['CANCELLED'] => 'bg-red-100 text-red-800',
                                        $orderStatuses['REFUNDED'] => 'bg-gray-100 text-gray-800',
                                        $orderStatuses['FAILED'] => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ __('orders.status.' . $order->payment_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ __('orders.no_orders') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
