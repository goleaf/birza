<!-- start table -->
<table class="min-w-full divide-y divide-gray-200">
    {{--
    <!-- start table header -->
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product.image') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product.price') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product.is_organic') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product.is_active') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product.stock') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ __('product.actions') }}
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
                    @if ($product->product_image)
                        <img 
                            src="{{ Storage::url('products/' . $product->product_image) }}" 
                            alt="{{ $product->name }}"
                            class="h-10 w-10 rounded-lg object-cover"
                        >
                    @else
                        <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </td>
                <!-- end image cell -->

                <!-- start price cell -->
                <td class="px-6 py-4">
                    {{ number_format($product->price, 2) }} € / {{ $product->unit }}
                </td>
                <!-- end price cell -->

                <!-- start organic cell -->
                <td class="px-6 py-4">
                    <span 
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_organic ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                    >
                        {{ $product->is_organic ? __('common.yes') : __('common.no') }}
                    </span>
                </td>
                <!-- end organic cell -->

                <!-- start active cell -->
                <td class="px-6 py-4">
                    <span 
                        class="px-2 inline-flex text-xs leading-5 font-semibold {{ $product->is_active ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}"
                    >
                        {{ $product->is_active ? __('product.active') : __('product.inactive') }}
                    </span>
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
                            <a 
                                href="{{ route('seller.products.edit', $product) }}"
                                class="px-3 py-1 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 transition-colors"
                            >
                                {{ __('product.edit') }}
                            </a>
                            <!-- end edit button -->
                        @endif

                        <!-- start delete/restore buttons -->
                        <div class="flex space-x-2">
                            @if ($product->deleted_at)
                                <!-- start restore button -->
                                <button 
                                    onclick="confirmRestore({{ $product->id }})"
                                    class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600"
                                >
                                    {{ __('product.restore') }}
                                </button>
                                <!-- end restore button -->
                            @else
                                <!-- start delete button -->
                                <button 
                                    onclick="confirmSoftDelete({{ $product->id }})"
                                    class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600"
                                >
                                    {{ __('product.soft_delete') }}
                                </button>
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

<!-- start javascript -->
<script>
    function confirmSoftDelete(productId) {
        if (confirm("{{ __('product.soft_delete_confirmation') }}")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('seller.products.destroy', '') }}/" + productId;
            form.innerHTML =
                '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function confirmRestore(productId) {
        if (confirm("{{ __('product.restore_confirmation') }}")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('seller.products.restore', '') }}/" + productId;
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
<!-- end javascript -->
