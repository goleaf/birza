<!-- start table -->
<table class="min-w-full divide-y divide-gray-200">
    {{--
    <!-- start table header -->
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_image_2') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_price') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_is_organic') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_is_active') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_stock') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_actions') }}
            </th>
        </tr>
    </thead>
    <!-- end table header -->
    --}}
    
    <!-- start table body -->
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($products as $product)
            <tr>
                <!-- start image cell -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <img
                        src="{{ $product->imageUrl('thumb') }}"
                        alt="{{ $product->name }}"
                        class="h-10 w-10 rounded-lg object-cover"
                        loading="lazy"
                        width="160"
                        height="160"
                    >
                </td>
                <!-- end image cell -->

                <!-- start price cell -->
                <td class="px-6 py-4">
                    {{ number_format($product->price, 2) }} € / {{ $product->unit }}
                </td>
                <!-- end price cell -->

                <!-- start organic cell -->
                <td class="px-6 py-4">
                    <x-ui.badge
                        :value="$product->is_organic ? __('common_yes') : __('common_no')"
                        :color="$product->is_organic ? 'success' : 'neutral'"
                        soft
                        sm
                        class="font-semibold"
                    />
                </td>
                <!-- end organic cell -->

                <!-- start active cell -->
                <td class="px-6 py-4">
                    <x-ui.badge
                        :value="$product->is_active ? __('product_active') : __('product_inactive')"
                        :color="$product->is_active ? 'info' : 'error'"
                        soft
                        sm
                        class="font-semibold"
                    />
                </td>
                <!-- end active cell -->

                <!-- start stock cell -->
                <td class="px-6 py-4">
                    {{ $product->stock }}
                </td>
                <!-- end stock cell -->

                <!-- start actions cell -->
                <td class="px-6 py-4 text-sm font-medium">
                    <!-- start actions container -->
                    <div class="flex space-x-2">
                        @if (!$product->deleted_at)
                            <!-- start edit button -->
                            <x-ui.button
                                xs
                                flat
                                primary
                                :href="route('seller.products.edit', $product)"
                                :label="__('product_edit')"
                            />
                            <!-- end edit button -->
                        @endif

                        <!-- start delete/restore buttons -->
                        <div class="flex space-x-2">
                            @if ($product->deleted_at)
                                <!-- start restore button -->
                                <x-ui.button
                                    xs
                                    flat
                                    positive
                                    :label="__('product_restore')"
                                    wire:click="confirmRestoreProduct({{ $product->id }})"
                                    spinner="confirmRestoreProduct"
                                />
                                <!-- end restore button -->
                            @else
                                <!-- start delete button -->
                                <x-ui.button
                                    xs
                                    flat
                                    negative
                                    :label="__('product_soft_delete')"
                                    wire:click="confirmSoftDeleteProduct({{ $product->id }})"
                                    spinner="confirmSoftDeleteProduct"
                                />
                                <!-- end delete button -->
                            @endif
                        </div>
                        <!-- end delete/restore buttons -->
                    </div>
                    <!-- end actions container -->
                </td>
                <!-- end actions cell -->
            </tr>
        @endforeach
    </tbody>
    <!-- end table body -->
</table>
<!-- end table -->
