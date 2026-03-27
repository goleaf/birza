<x-backend.page :title="__('products.title')">
    <x-slot:actions>
        <a href="{{ route('backend.products.create') }}" class="btn btn-primary btn-sm">
            {{ __('products.actions.create') }}
        </a>
    </x-slot:actions>


        <!-- Filters Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <form action="{{ route('backend.products.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">{{ __('common_search') }}</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">{{ __('product_category') }}</label>
                            <select name="category" id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('common_all') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Seller Filter -->
                        <div>
                            <label for="seller" class="block text-sm font-medium text-gray-700">{{ __('product_seller') }}</label>
                            <select name="seller" id="seller" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('common_all') }}</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->company_name ?: $seller->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <label for="min_price" class="block text-sm font-medium text-gray-700">{{ __('product_min_price') }}</label>
                            <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" step="0.01"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="max_price" class="block text-sm font-medium text-gray-700">{{ __('product_max_price') }}</label>
                            <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" step="0.01"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('common_status') }}</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('common_all') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('common_active') }}</option>
                                <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>{{ __('common_trashed') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="flex justify-between items-center mt-4">
                        <div class="flex-1 mr-4">
                            <label for="sort" class="block text-sm font-medium text-gray-700">{{ __('common_sort_by') }}</label>
                            <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common_newest') }}</option>
                                <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common_oldest') }}</option>
                                <option value="price,asc" {{ request('sort') === 'price,asc' ? 'selected' : '' }}>{{ __('product_price_low_high') }}</option>
                                <option value="price,desc" {{ request('sort') === 'price,desc' ? 'selected' : '' }}>{{ __('product_price_high_low') }}</option>
                                <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common_name_az') }}</option>
                                <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common_name_za') }}</option>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <x-button type="submit" primary :label="__('common_filter')" />
                            <x-button flat :href="route('backend.products.index')" :label="__('common_reset')" />
                        </div>
                    </div>

                    <div class="form-control">
                        <label for="seller" class="label">
                            <span class="label-text">{{ __('product.seller') }}</span>
                        </label>
                        <select name="seller" id="seller" class="select select-bordered w-full">
                            <option value="">{{ __('common.all') }}</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->company_name ?: $seller->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <label for="min_price" class="label">
                            <span class="label-text">{{ __('product.min_price') }}</span>
                        </label>
                        <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" step="0.01"
                            class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label for="max_price" class="label">
                            <span class="label-text">{{ __('product.max_price') }}</span>
                        </label>
                        <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" step="0.01"
                            class="input input-bordered w-full">
                    </div>

                    <div class="form-control">
                        <label for="status" class="label">
                            <span class="label-text">{{ __('common.status') }}</span>
                        </label>
                        <select name="status" id="status" class="select select-bordered w-full">
                            <option value="">{{ __('common.all') }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('common.active') }}</option>
                            <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>{{ __('common.trashed') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="form-control md:col-span-2">
                        <label for="sort" class="label">
                            <span class="label-text">{{ __('common.sort_by') }}</span>
                        </label>
                        <select name="sort" id="sort" class="select select-bordered w-full">
                            <option value="created_at,desc" {{ request('sort') === 'created_at,desc' ? 'selected' : '' }}>{{ __('common.newest') }}</option>
                            <option value="created_at,asc" {{ request('sort') === 'created_at,asc' ? 'selected' : '' }}>{{ __('common.oldest') }}</option>
                            <option value="price,asc" {{ request('sort') === 'price,asc' ? 'selected' : '' }}>{{ __('product.price_low_high') }}</option>
                            <option value="price,desc" {{ request('sort') === 'price,desc' ? 'selected' : '' }}>{{ __('product.price_high_low') }}</option>
                            <option value="name,asc" {{ request('sort') === 'name,asc' ? 'selected' : '' }}>{{ __('common.name_az') }}</option>
                            <option value="name,desc" {{ request('sort') === 'name,desc' ? 'selected' : '' }}>{{ __('common.name_za') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            {{ __('common.filter') }}
                        </button>
                        <a href="{{ route('backend.products.index') }}" class="btn btn-ghost btn-sm">
                            {{ __('common.reset') }}
                        </a>
                    </div>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            @if($products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>{{ __('product.image') }}</th>
                                <th>{{ __('product.name') }}</th>
                                <th>{{ __('product.category') }}</th>
                                <th>{{ __('product.seller') }}</th>
                                <th class="text-right">{{ __('product.price') }}</th>
                                <th class="text-center">{{ __('common.status') }}</th>
                                <th class="text-right">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('product_image_2') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('product_name') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('product_category') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('product_seller') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('product_price') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('common_status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('common_actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img src="{{ Storage::url('products/' . $product->product_image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="w-16 h-16 object-cover rounded">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            @if($product->description)
                                                <div class="text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $product->category->category_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $product->seller->company_name ?: $product->seller->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="text-sm font-medium text-gray-900">€{{ number_format($product->price, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($product->trashed())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ __('common_trashed') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('common_active') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if ($product->trashed())
                                                <x-button
                                                    xs
                                                    flat
                                                    positive
                                                    wire:click="restoreProduct({{ $product->id }})"
                                                    :label="__('common_restore')"
                                                />
                                                <x-button
                                                    xs
                                                    flat
                                                    negative
                                                    wire:click="confirmForceDeleteProduct({{ $product->id }})"
                                                    :label="__('common_force_delete')"
                                                />
                                            @else
                                                <a href="{{ route('backend.products.edit', $product) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    {{ __('common_edit') }}
                                                </a>
                                                <x-button
                                                    xs
                                                    flat
                                                    negative
                                                    wire:click="confirmDeleteProduct({{ $product->id }})"
                                                    :label="__('common_delete')"
                                                />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('products_no_products') }}</h3>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-backend.page>
