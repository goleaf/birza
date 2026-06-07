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
    
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_image_2') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_name') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product_category') }}
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

    <!-- start table body -->
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($products as $product)
            <tr>
                <!-- start image cell -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <img 
                        src="{{ $product->imageUrl('thumb') }}" 
                        alt="{{ $product->name }}"
                        class="h-10 w-10 rounded-lg object-cover"
                        loading="lazy"
                    >
                </td>
                <!-- end image cell -->

                <td class="px-6 py-4 font-medium text-gray-900">
                    {{ $product->name }}
                </td>

                <td class="px-6 py-4 text-gray-600">
                    {{ $product->category?->getTranslation('category_name', app()->getLocale()) ?? __('common_not_specified') }}
                </td>

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
                        :value="$product->statusLabel()"
                        :color="$product->statusBadgeColor()"
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
        @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    {{ __('product_no_products_found') }}
                </td>
            </tr>
        @endforelse
    </tbody>
    <!-- end table body -->
</table>
<!-- end table -->
