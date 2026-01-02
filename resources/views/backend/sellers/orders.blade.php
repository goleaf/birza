<x-backend.page :title="__('orders.for_seller', ['name' => $seller->name])">
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>{{ __('orders.id') }}</th>
                        <th>{{ __('orders.customer') }}</th>
                        <th>{{ __('orders.products') }}</th>
                        <th>{{ __('orders.date') }}</th>
                        <th>{{ __('orders.actions') }}</th>
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
                            <td>
                                <a href="{{ route('backend.orders.show', $order) }}" class="btn btn-ghost btn-xs">
                                    {{ __('orders.view_details') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60">
                                {{ __('orders.none_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-backend.page>
