<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('products_title') }}</h1>
        <a href="{{ route('backend.products.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            {{ __('products_actions_create') }}
        </a>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <form action="{{ route('backend.products.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">{{ __('common_search') }}</label>
                    <input
                        type="text"
                        name="search"
                        id="search"
                        value="{{ request('search') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">{{ __('product_category') }}</label>
                    <select name="category" id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('common_all') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->getTranslation('category_name', app()->getLocale()) }}
                            </option>
                            @foreach ($category->subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" {{ request('category') == $subcategory->id ? 'selected' : '' }}>
                                    -- {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="seller" class="block text-sm font-medium text-gray-700">{{ __('product_seller') }}</label>
                    <select name="seller" id="seller" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('common_all') }}</option>
                        @foreach ($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->company_name ?: $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="min_price" class="block text-sm font-medium text-gray-700">{{ __('product_min_price') }}</label>
                    <input
                        type="number"
                        name="min_price"
                        id="min_price"
                        value="{{ request('min_price') }}"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="max_price" class="block text-sm font-medium text-gray-700">{{ __('product_max_price') }}</label>
                    <input
                        type="number"
                        name="max_price"
                        id="max_price"
                        value="{{ request('max_price') }}"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">{{ __('common_status') }}</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('common_all') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('common_active') }}</option>
                        <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>{{ __('common_trashed') }}</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="md:w-80">
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

                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        {{ __('common_filter') }}
                    </button>
                    <a href="{{ route('backend.products.index') }}" class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                        {{ __('common_reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('product_image_2') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('product_name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('product_category') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('product_seller') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('product_price') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('common_status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($products as $product)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($product->product_image)
                                    <img
                                        src="{{ Storage::url('products/' . $product->product_image) }}"
                                        alt="{{ $product->name }}"
                                        class="h-16 w-16 rounded object-cover"
                                    >
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded bg-gray-100 text-xs text-gray-400">
                                        N/A
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                @if ($product->description)
                                    <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($product->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                {{ $product->category?->getTranslation('category_name', app()->getLocale()) ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                {{ $product->seller?->company_name ?: $product->seller?->name ?: '-' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-gray-900">
                                €{{ number_format($product->price, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                @if ($product->trashed())
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        {{ __('common_trashed') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ __('common_active') }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
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
                                    <a href="{{ route('backend.products.edit', $product) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                {{ __('products_no_products') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $products->links() }}
        </div>
    </div>
</div>
