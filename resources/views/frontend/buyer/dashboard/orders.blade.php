{{-- 
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wider">{{ __('dashboard_total_orders') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $ordersData['insights']['totalOrders'] }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <div class="h-1 bg-blue-100 rounded-full">
                <div class="h-1 bg-blue-500 rounded-full" style="width: 100%"></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wider">{{ __('dashboard_success_rate') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $ordersData['insights']['successRate'] }}%</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wider">{{ __('dashboard_avg_order_value') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">€{{ number_format($ordersData['insights']['averageOrderValue'], 2) }}</p>
            </div>
            <div class="bg-purple-100 rounded-full p-3">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wider">{{ __('dashboard_total_spent') }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">€{{ number_format($ordersData['totalSpent'], 2) }}</p>
            </div>
            <div class="bg-indigo-100 rounded-full p-3">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>
--}}

<!-- start orders container -->
<div 
    class="bg-white rounded-lg shadow-lg p-6 mt-6"
>
    <!-- start header container -->
    <div 
        class="flex items-center justify-between mb-6"
    >
        <!-- start title -->
        <h4 
            class="text-lg font-semibold text-gray-800"
        >
            {{ __('dashboard_recent_orders') }}
        </h4>
        <!-- end title -->

        <!-- start view all link -->
        <a 
            href="{{ route('buyer.orders.index') }}" 
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
            {{ __('common_view_all') }} →
        </a>
        <!-- end view all link -->
    </div>
    <!-- end header container -->

    <!-- start table container -->
    <div class="overflow-x-auto">
        <!-- start table -->
        <table class="min-w-full">
            <!-- start table head -->
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('orders_id') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('common_date') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('common_status') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('orders_total') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('common_actions') }}
                    </th>
                </tr>
            </thead>
            <!-- end table head -->

            <!-- start table body -->
            <tbody class="divide-y divide-gray-200">
                @forelse($ordersData['recentActivity']['recentOrders'] as $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $order->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $order->created_at->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"> 
                            <x-ui.badge
                                :value="$order->paymentStatusLabel()"
                                :color="$order->paymentStatusUiColor()"
                                soft
                                class="font-medium"
                            />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            €{{ number_format($order->order_total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a 
                                href="{{ route('buyer.orders.show', $order) }}" 
                                class="text-blue-600 hover:text-blue-800"
                            >
                                {{ __('orders_view_details') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            {{ __('dashboard_no_orders') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <!-- end table body -->
        </table>
        <!-- end table -->
    </div>
    <!-- end table container -->
</div>
<!-- end orders container -->
