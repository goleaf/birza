<x-backend.page :title="$seller->company_name" :description="$seller->email">
    <x-slot:actions>
        <a href="{{ route('backend.sellers.edit', $seller) }}" class="btn btn-ghost">
            {{ __('common.edit') }}
        </a>
    </x-slot:actions>

    <div class="space-y-6">
        <x-ui.card>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <div class="text-sm text-base-content/60">{{ __('sellers.contact_person') }}</div>
                    <div class="text-lg font-semibold">{{ $seller->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">{{ __('sellers.email') }}</div>
                    <div class="text-lg font-semibold">{{ $seller->email }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">{{ __('sellers.vat_code') }}</div>
                    <div class="text-lg font-semibold">{{ $seller->vat_code ?: '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">{{ __('sellers.phone') }}</div>
                    <div class="text-lg font-semibold">{{ $seller->phone ?: '-' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm text-base-content/60">{{ __('sellers.address') }}</div>
                    <div class="text-lg font-semibold">{{ $seller->address ?: '-' }}</div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="px-6 pt-6">
                <h2 class="text-lg font-semibold">{{ __('sellers.products') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>{{ __('products.name') }}</th>
                            <th class="text-right">{{ __('products.price') }}</th>
                            <th class="text-right">{{ __('products.times_ordered') }}</th>
                            <th class="text-center">{{ __('products.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-full object-cover">
                                        @endif
                                        <a href="{{ route('backend.products.show', $product) }}" class="link link-primary font-medium">
                                            {{ $product->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="text-right">€{{ number_format($product->price, 2) }}</td>
                                <td class="text-right">{{ $product->order_items_count }}</td>
                                <td class="text-center">
                                    <span class="badge badge-outline {{ $product->is_active ? 'badge-success' : 'badge-error' }}">
                                        {{ $product->is_active ? __('common.active') : __('common.inactive') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-base-content/60">
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
        </x-ui.card>

        <x-ui.card>
            <div class="px-6 pt-6">
                <h2 class="text-lg font-semibold">{{ __('sellers.orders') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>{{ __('orders.id') }}</th>
                            <th>{{ __('orders.buyer') }}</th>
                            <th class="w-1/3">{{ __('orders.items') }}</th>
                            <th class="text-right">{{ __('orders.total') }}</th>
                            <th class="text-center">{{ __('orders.status') }}</th>
                            <th>{{ __('orders.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('backend.orders.show', $order) }}" class="link link-primary font-medium">
                                        {{ $order->id }}
                                    </a>
                                </td>
                                <td>
                                    @if($order->buyer)
                                        <div class="font-medium">{{ $order->buyer->company_name }}</div>
                                    @else
                                        <span class="text-base-content/60 italic">{{ __('orders.buyer_not_found') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="space-y-2 text-sm">
                                        @foreach($order->orderItems as $item)
                                            <div class="flex justify-between">
                                                <div class="font-medium">
                                                    {{ $item->product->name }}
                                                    <span class="text-base-content/60 ml-1">×{{ $item->quantity }}</span>
                                                </div>
                                                <div>€{{ number_format($item->total_price, 2) }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-right font-medium">€{{ number_format($order->order_total, 2) }}</td>
                                <td class="text-center">
                                    @php
                                        $statusClass = match($order->payment_status) {
                                            $orderStatuses['PENDING'] => 'badge-warning',
                                            $orderStatuses['PAID'] => 'badge-success',
                                            $orderStatuses['PROCESSING'] => 'badge-info',
                                            $orderStatuses['SHIPPED'] => 'badge-primary',
                                            $orderStatuses['DELIVERED'] => 'badge-success',
                                            $orderStatuses['CANCELLED'] => 'badge-error',
                                            $orderStatuses['REFUNDED'] => 'badge-neutral',
                                            $orderStatuses['FAILED'] => 'badge-error',
                                            default => 'badge-neutral'
                                        };
                                    @endphp
                                    <span class="badge badge-outline {{ $statusClass }}">
                                        {{ __('orders.status.' . $order->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-sm text-base-content/60">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/60">
                                    {{ __('orders.no_orders') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-backend.page>
