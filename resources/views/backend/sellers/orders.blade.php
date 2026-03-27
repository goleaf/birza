<div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('orders_for_seller', ['name' => $seller->name]) }}</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_id') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_products') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('orders_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>
                                <div class="font-medium">{{ $order->buyer->name }}</div>
                                <div class="text-sm text-base-content/60">{{ $order->buyer->email }}</div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    @foreach($order->items as $item)
                                        <div class="mb-1">
                                            {{ $item->product->name }}
                                            <span class="text-base-content/60">
                                                ({{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} {{ $order->currency }})
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-sm text-base-content/60">
                                {{ $order->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="{{ route('backend.orders.show', $order) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('orders_view_details') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                {{ __('orders_none_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-backend.page>
