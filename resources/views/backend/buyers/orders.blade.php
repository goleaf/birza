<x-backend.page :title="$buyer->company_name">
    <x-slot:actions>
        <x-button flat :href="route('backend.buyers.index')" :label="__('backend.common.back')" />
    </x-slot:actions>

    <x-ui.card>
        <p class="text-sm text-gray-500">{{ __('backend.buyers.orders.title') }}</p>

        @if ($orders->count() > 0)
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend.orders.order_number') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend.orders.date') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend.orders.total') }}</th>
                            <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend.orders.status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('backend.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">#{{ $order->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">€{{ number_format($order->items->sum('total_price'), 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                        @if($order->payment_status === 'paid') bg-green-100 text-green-800
                                        @elseif($order->payment_status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ __('backend.orders.status_' . $order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">
                                    <x-button xs flat :href="route('backend.orders.show', $order)" :label="__('backend.common.view')" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $orders->links() }}
            </div>
        @else
            <div class="py-12 text-center">
                <div class="text-sm text-gray-500">{{ __('backend.orders.no_orders') }}</div>
            </div>
        @endif
    </x-ui.card>
</x-backend.page>
