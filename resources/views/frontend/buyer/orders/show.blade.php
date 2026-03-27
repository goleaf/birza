<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <!-- start order header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <!-- start header content -->
            <div class="flex justify-between items-center mb-6">
                <!-- start left side -->
                <div>
                    <!-- start title -->
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ __('orders_order_details') }} #{{ $order->id }}
                    </h1>
                    <!-- end title -->
                    
                    <!-- start date -->
                    <p class="text-gray-600">
                        {{ __('orders_placed_on') }}: {{ $order->created_at }}
                    </p>
                    <!-- end date -->
                </div>
                <!-- end left side -->
                
                <!-- start right side -->
                <div class="flex space-x-4">
                    <!-- start back link -->
                    <a 
                        href="{{ route('buyer.orders.index') }}" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        {{ __('common_back_to_orders') }}
                    </a>
                    <!-- end back link -->
                    
                    <!-- start cancel form -->
                    @if($order->payment_status === \App\Models\Order::STATUS['PENDING'])
                        <x-button
                            negative
                            :label="__('orders_cancel_order')"
                            wire:click="confirmCancelOrder"
                            spinner="confirmCancelOrder"
                        />
                    @endif
                    <!-- end cancel form -->
                </div>
                <!-- end right side -->
            </div>
            <!-- end header content -->

            <!-- start order status -->
            <div class="mb-6">
                <!-- start status badge -->
                <div 
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($order->payment_status === \App\Models\Order::STATUS['PENDING']) bg-yellow-100 text-yellow-800
                    @elseif($order->payment_status === \App\Models\Order::STATUS['PAID']) bg-green-100 text-green-800
                    @elseif($order->payment_status === \App\Models\Order::STATUS['CANCELLED']) bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif"
                >
                    {{ __('orders_status_3_' . strtolower($order->payment_status)) }}
                </div>
                <!-- end status badge -->
            </div>
            <!-- end order status -->
        </div>
        <!-- end order header -->

        <!-- start order items -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
            <!-- start items header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ __('orders_order_items') }}
                </h2>
            </div>
            <!-- end items header -->
            
            <!-- start table -->
            <table class="min-w-full divide-y divide-gray-200">
                <!-- start table head -->
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_product') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_seller') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_quantity') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_unit_price') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('orders_total') }}
                        </th>
                    </tr>
                </thead>
                <!-- end table head -->

                <!-- start table body -->
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- start product info -->
                                <div class="flex items-center">
                                    <!-- start product image -->
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img src="{{ Storage::url('products/' . $item->product->product_image) }}" class="h-10 w-10 rounded-full object-cover">
                                    </div>
                                    <!-- end product image -->
                                    
                                    <!-- start product name -->
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $item->product->name }}
                                        </div>
                                    </div>
                                    <!-- end product name -->
                                </div>
                                <!-- end product info -->
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $item->seller->company_name }}
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
                <!-- end table body -->
                
                <!-- start table footer -->
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            {{ __('orders_order_total') }}:
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                            {{ number_format($order->order_total, 2) }} € <br>
                            <span class="text-gray-500 font-normal">({{ __('product_price_without_vat') }})</span>
                        </td> 
                    </tr>
                </tfoot>
                <!-- end table footer -->
            </table>
            <!-- end table -->
        </div>
        <!-- end order items -->
    </div>
    <!-- end main container -->
</div>
<!-- end section -->