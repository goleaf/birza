<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <!-- start orders list -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <!-- start filters form -->
            <form method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
                <!-- start status filter -->
                <div class="w-full sm:w-48">
                    <label
                        for="status"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        {{ __('common.status') }}
                    </label>
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
                                {{ (string)$filters['status'] == (string)$value ? 'selected' : '' }}
                            >
                                {{ __('orders.status_' . strtolower($key)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- end status filter -->

                <!-- start date from filter -->
                <div class="w-full sm:w-48">
                    <label
                        for="date_from"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
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
                <!-- end date from filter -->

                <!-- start date to filter -->
                <div class="w-full sm:w-48">
                    <label
                        for="date_to"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
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
                <!-- end date to filter -->

                <!-- start filter button -->
                <div class="w-full sm:w-auto sm:self-end">
                    <label
                        for="filter_button"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        &nbsp;
                    </label>
                    <button
                        type="submit"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                        name="filter_button"
                    >
                        {{ __('common.filter') }}
                    </button>
                </div>
                <!-- end filter button -->
            </form>
            <!-- end filters form -->
        </div>

        @if ($ordersData['all']->count() > 0)
            <!-- start table container -->
            <div class="overflow-x-auto">
                <!-- start orders table -->
                <table class="min-w-full divide-y divide-gray-200">
                    <!-- start table header -->
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.order_number') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.table_date') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('orders.table_amount') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common.status') }}
                            </th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <!-- end table header -->

                    <!-- start table body -->
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $totalAmount = 0;
                        @endphp
                        @foreach ($ordersData['all'] as $order)
                            @php
                                $totalAmount += $order->order_total ?? $order->total_price;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($order->order_total ?? $order->total_price, 2) }} €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $order->payment_status === \App\Models\Order::STATUS['PENDING'] ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->payment_status === \App\Models\Order::STATUS['PAID'] ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->payment_status === \App\Models\Order::STATUS['DELIVERED'] ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->payment_status === \App\Models\Order::STATUS['SHIPPED'] ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->payment_status === \App\Models\Order::STATUS['CANCELLED'] ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $order->payment_status === \App\Models\Order::STATUS['REFUNDED'] ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $order->payment_status === \App\Models\Order::STATUS['PROCESSING'] ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $order->payment_status === \App\Models\Order::STATUS['FAILED'] ? 'bg-gray-100 text-gray-800' : '' }}"
                                    >
                                        {{ __('orders.status_' . strtolower($order->payment_status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a
                                        href="{{ route('seller.orders.show', $order) }}"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        {{ __('orders.view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        <!-- start total row -->
                        <tr class="bg-gray-50 font-bold">
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                {{ __('orders.total') }}:
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ number_format($totalAmount, 2) }} €
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <!-- end total row -->
                    </tbody>
                    <!-- end table body -->
                </table>
                <!-- end orders table -->
            </div>
            <!-- end table container -->
        @else
            <!-- start no orders message -->
            <div class="text-center py-8 text-gray-500">
                {{ __('orders.no_orders') }}
            </div>
            <!-- end no orders message -->
        @endif
    </div>
    <!-- end main container -->
</div>
<!-- end section -->
