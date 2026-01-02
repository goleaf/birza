<x-backend.page :title="__('orders.title')">
    <div class="space-y-6">
        <x-ui.card>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders.stats_total_orders') }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totalOrders }}</p>
                        </div>
                        <div class="rounded-full bg-blue-100 p-3">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders.pending_orders') }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $pendingOrders }}</p>
                        </div>
                        <div class="rounded-full bg-yellow-100 p-3">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders.stats_avg_order_value') }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-800">€{{ number_format($averageOrderValue, 2) }}</p>
                        </div>
                        <div class="rounded-full bg-purple-100 p-3">
                            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders.stats_total_revenue') }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-800">€{{ number_format($totalRevenue, 2) }}</p>
                        </div>
                        <div class="rounded-full bg-green-100 p-3">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('orders.order_list') }}</h2>
                <form action="{{ route('backend.orders.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{ __('orders.filter_all_status') }}</option>
                        @foreach (\App\Models\Order::STATUS as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ __('orders.status_' . strtolower($status)) }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="{{ __('orders.date_from') }}"
                    >
                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="{{ __('orders.date_to') }}"
                    >

                    <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('orders.filter') }}
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('orders.table_order_id') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('orders.table_date') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('buyer.buyer') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('orders.status_') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('orders.table_amount') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                {{ __('common.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    {{ $order->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    @if ($order->buyer)
                                        <div class="flex items-center">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $order->buyer->name }}</div>
                                                <div class="text-gray-500">{{ $order->buyer->email }}</div>
                                            </div>
                                            @if ($order->buyer->trashed())
                                                <span class="ml-2 inline-flex items-center rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                    {{ __('common.deleted') }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-500">{{ __('common.not_specified') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                        @if($order->payment_status === \App\Models\Order::STATUS['PENDING']) bg-yellow-100 text-yellow-800
                                        @elseif($order->payment_status === \App\Models\Order::STATUS['PAID']) bg-green-100 text-green-800
                                        @elseif($order->payment_status === \App\Models\Order::STATUS['CANCELLED']) bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif"
                                    >
                                        {{ __('orders.status_' . strtolower($order->payment_status)) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    €{{ number_format($order->order_total, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    <a href="{{ route('backend.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ __('common.view') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">
                                    {{ __('orders.no_orders_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="mt-4 flex justify-center">
                    <div class="text-sm">
                        {{ $orders->links() }}
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>
</x-backend.page>
