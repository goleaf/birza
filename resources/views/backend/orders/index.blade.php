<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow-lg">
            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders_stats_total_orders') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $totalOrders }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow-lg">
            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders_pending_orders') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $pendingOrders }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow-lg">
            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders_stats_avg_order_value') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">€{{ number_format($averageOrderValue, 2) }}</p>
        </div>
        <div class="rounded-lg bg-white p-6 shadow-lg">
            <p class="text-sm uppercase tracking-wider text-gray-500">{{ __('orders_stats_total_revenue') }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">€{{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-lg">
        <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('orders_order_list') }}</h1>

            <form action="{{ route('backend.orders.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row md:items-center">
                <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">{{ __('orders_filter_all_status') }}</option>
                    @foreach (\App\Models\Order::STATUS as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ __('orders_status_3_' . strtolower($status)) }}
                        </option>
                    @endforeach
                </select>

                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >

                <button type="submit" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    {{ __('orders_filter') }}
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('orders_table_order_id') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('orders_table_date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('buyer_buyer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('orders_status_3') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('orders_table_amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                @if ($order->buyer)
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $order->buyer->name }}</div>
                                        <div class="text-gray-500">{{ $order->buyer->email }}</div>
                                    </div>
                                @else
                                    <span class="text-gray-500">{{ __('common_not_specified') }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                    @if ($order->payment_status === \App\Models\Order::STATUS['PENDING']) bg-yellow-100 text-yellow-800
                                    @elseif ($order->payment_status === \App\Models\Order::STATUS['PAID']) bg-green-100 text-green-800
                                    @elseif ($order->payment_status === \App\Models\Order::STATUS['CANCELLED']) bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ __('orders_status_3_' . strtolower($order->payment_status)) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">€{{ number_format($order->order_total, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                <a href="{{ route('backend.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('common_view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ __('orders_no_orders_found') }}
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
    </div>
</div>
