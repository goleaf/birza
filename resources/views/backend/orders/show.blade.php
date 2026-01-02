<x-backend.page :title="__('orders.order_details') . ' #' . $order->id">
    <div class="space-y-6">
        <x-ui.card>
            <div class="mb-6">
                <p class="text-gray-600">
                    {{ __('orders.placed_on') }}: {{ $order->created_at->format('Y-m-d H:i') }}
                </p>
            </div>

            <div class="mb-6">
                <div
                    class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                    @if($order->payment_status === \App\Models\Order::STATUS['PENDING']) bg-yellow-100 text-yellow-800
                    @elseif($order->payment_status === \App\Models\Order::STATUS['PAID']) bg-green-100 text-green-800
                    @elseif($order->payment_status === \App\Models\Order::STATUS['CANCELLED']) bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif"
                >
                    {{ __('orders.status_') }}: {{ __('orders.status_' . strtolower($order->payment_status)) }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-900">{{ __('buyer.buyer_information') }}</h2>
                    <div class="text-sm text-gray-600">
                        @if ($order->buyer)
                            <p class="font-medium text-gray-900">{{ $order->buyer->name }}</p>
                            <p>{{ $order->buyer->email }}</p>
                            <p>{{ $order->buyer->company_name }}</p>
                            <p>{{ $order->buyer->address }}</p>
                            <p>{{ $order->buyer->phone }}</p>
                            @if ($order->buyer->trashed())
                                <span class="mt-1 text-xs text-red-500">({{ __('common.deleted') }})</span>
                            @endif
                        @else
                            <p class="text-gray-500">{{ __('common.not_specified') }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-900">{{ __('orders.payment_information') }}</h2>
                    <div class="text-sm text-gray-600">
                        <p><span class="font-medium">{{ __('orders.payment_method') }}:</span> {{ $order->payment_method ?: __('common.not_specified') }}</p>
                        <p><span class="font-medium">{{ __('orders.status_') }}:</span> {{ __('orders.status_' . strtolower($order->payment_status)) }}</p>
                        <p><span class="font-medium">{{ __('orders.order_total') }}:</span> {{ number_format($order->order_total, 2) }} €</p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('orders.order_items')">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('orders.product') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('orders.seller') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('orders.quantity') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('orders.unit_price') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('orders.table_amount') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($order->orderItems as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if ($item->product)
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img
                                                src="{{ Storage::url('products/' . $item->product->product_image) }}"
                                                class="h-10 w-10 rounded-full object-cover"
                                                alt="{{ $item->product->name }}"
                                            >
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->product->name }}
                                                @if ($item->product->trashed())
                                                    <span class="text-xs text-red-500">({{ __('common.deleted') }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="ml-4">
                                            <div class="text-sm text-gray-500">{{ __('common.not_specified') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if ($item->seller)
                                    <div>
                                        <a href="{{ route('backend.sellers.show', $item->seller) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $item->seller->company_name }}
                                        </a>
                                        @if ($item->seller->trashed())
                                            <span class="text-xs text-red-500">({{ __('common.deleted') }})</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-gray-500">{{ __('common.not_specified') }}</div>
                                @endif
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
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            {{ __('orders.order_total') }}:
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900">
                            {{ number_format($order->order_total, 2) }} €
                        </td>
                    </tr>
                </tfoot>
            </table>
        </x-ui.card>
    </div>
</x-backend.page>
