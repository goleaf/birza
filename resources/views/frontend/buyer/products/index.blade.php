<div>
    <!-- start bg container -->
    <div class="bg-white">
        <!-- start main -->
        <main class="w-full px-4 py-8 sm:px-6 lg:px-8">
            <x-buyer.breadcrumbs
                class="mb-6"
                :items="[
                    ['label' => __('common_products')],
                ]"
            />

            <!-- start flex container -->
            <div class="flex gap-6">
                <!-- start filters -->
                <div class="w-64 flex-shrink-0">
                    <!-- start form -->
                    <form class="space-y-6 bg-white p-4 rounded shadow">
                        <!-- start categories -->
                        <div class="space-y-1">
                            @foreach ($categories as $category)
                                <x-mary-collapse
                                    :open="$category->id == request('category') || $category->subcategories->contains('id', request('category'))"
                                    class="rounded-lg border border-gray-200 bg-white shadow-sm"
                                >
                                    <x-slot:heading class="text-sm font-semibold text-gray-900">
                                        {{ $category->getTranslation('category_name', app()->getLocale()) }}
                                    </x-slot:heading>

                                    <x-slot:content class="bg-gray-50">
                                        <div class="space-y-3">
                                            @foreach ($category->subcategories as $subcategory)
                                                <!-- start subcategory link -->
                                                <a
                                                    href="{{ route('buyer.products.index', ['category' => $subcategory->id]) }}"
                                                    class="block py-0.5 pl-2 text-sm hover:text-blue-600 {{ request('category') == $subcategory->id ? 'font-medium text-blue-600' : '' }}"
                                                >
                                                    {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                                </a>
                                                <!-- end subcategory link -->

                                                @if (request('category') == $subcategory->id)
                                                    <!-- start filters -->
                                                    <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                                                        @foreach ($subcategory->attributes as $attribute)
                                                            <!-- start attribute -->
                                                            <div class="mb-3">
                                                                <!-- start attribute name -->
                                                                <p class="mb-1 text-sm font-medium">
                                                                    {{ $attribute->name }}
                                                                </p>
                                                                <!-- end attribute name -->

                                                                @foreach ($attribute->values as $value)
                                                                    <!-- start checkbox label -->
                                                                    <label class="mb-1 flex items-center text-sm">
                                                                        <input
                                                                            type="checkbox"
                                                                            name="filters[{{ $attribute->id }}]"
                                                                            value="{{ $value->id }}"
                                                                            class="mr-2"
                                                                            {{ request("filters.$attribute->id") == $value->id ? 'checked' : '' }}
                                                                            {{ $attribute->is_required ? 'required' : '' }}
                                                                        >
                                                                        {{ $value->value }}
                                                                    </label>
                                                                    <!-- end checkbox label -->
                                                                @endforeach
                                                            </div>
                                                            <!-- end attribute -->
                                                        @endforeach
                                                    </div>
                                                    <!-- end filters -->
                                                @endif
                                            @endforeach
                                        </div>
                                    </x-slot:content>
                                </x-mary-collapse>
                            @endforeach
                        </div>
                        <!-- end categories -->

                        <!-- start additional filters -->
                        <div class="space-y-4">
                            <!-- start price range -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('product_price_range') }}</label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="number" 
                                        name="price_min" 
                                        value="{{ request('price_min') }}"
                                        placeholder="{{ __('product_min_price') }}"
                                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                    <span>-</span>
                                    <input 
                                        type="number" 
                                        name="price_max" 
                                        value="{{ request('price_max') }}"
                                        placeholder="{{ __('product_max_price') }}"
                                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                </div>
                            </div>
                            <!-- end price range -->

                            <!-- start stock range -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('product_stock_range') }}</label>
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="number" 
                                        name="stock_min" 
                                        value="{{ request('stock_min') }}"
                                        placeholder="{{ __('product_min_stock') }}"
                                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                    <span>-</span>
                                    <input 
                                        type="number" 
                                        name="stock_max" 
                                        value="{{ request('stock_max') }}"
                                        placeholder="{{ __('product_max_stock') }}"
                                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                </div>
                            </div>
                            <!-- end stock range -->

                            <!-- start country select -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('product_country') }}</label>
                                <select 
                                    name="country_of_origin"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">
                                        {{ __('product_select_country') }}
                                    </option>
                                    @foreach ($countries as $country)
                                        <option 
                                            value="{{ $country->id }}"
                                            {{ request('country_of_origin') == $country->id ? 'selected' : '' }}
                                        >
                                            {{ $country->getTranslation('country_name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- end country select -->

                            <!-- start organic select -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">{{ __('product_organic') }}</label>
                                <select 
                                    name="is_organic"
                                    class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">
                                        {{ __('product_organic_filter') }}
                                    </option>
                                    <option 
                                        value="1" 
                                        {{ request('is_organic') === '1' ? 'selected' : '' }}
                                    >
                                        {{ __('common_yes') }}
                                    </option>
                                    <option 
                                        value="0" 
                                        {{ request('is_organic') === '0' ? 'selected' : '' }}
                                    >
                                        {{ __('common_no') }}
                                    </option>
                                </select>
                            </div>
                            <!-- end organic select -->

                            <!-- start filter buttons -->
                            <div class="flex gap-2">
                                <button 
                                    type="submit" 
                                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                                >
                                    {{ __('common_filter') }}
                                </button>

                                @if (request()->anyFilled(['price_min', 'price_max', 'country_of_origin', 'is_organic', 'stock_min', 'stock_max']))
                                    <a 
                                        href="{{ route('buyer.products.index', ['category' => request('category')]) }}"
                                        class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-center"
                                    >
                                        {{ __('common_clear') }}
                                    </a>
                                @endif
                            </div>
                            <!-- end filter buttons -->
                        </div>
                        <!-- end additional filters -->

                    </form>
                    <!-- end form -->
                </div>
                <!-- end filters -->

                <!-- start products section -->
                <div class="flex-1">
                    <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-start">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                id="live-search"
                                placeholder="{{ __('product_search_placeholder') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div
                                id="search-results"
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden"
                            >
                                <div class="max-h-96 overflow-y-auto">
                                    <!-- Results will be populated here -->
                                </div>
                            </div>
                        </div>

                        <x-ui.button
                            :href="route('buyer.compare.index')"
                            secondary
                            outline
                            icon="scale"
                            class="justify-center"
                            :label="__('compare.actions.view_list', ['count' => $comparisonCount, 'limit' => $comparisonLimit])"
                        />
                    </div>

                    @if ($products->isEmpty())
                        <!-- start empty message -->
                        <p class="text-center text-gray-500 py-8">
                            {{ __('product_no_products_found') }}
                        </p>
                        <!-- end empty message -->
                    @else
                        <!-- start products grid -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
	                            @foreach ($products as $product)
	                                @php
	                                    $isCompared = in_array((int) $product->id, $comparedProductIds, true);
	                                    $isWishlisted = in_array((int) $product->id, $wishlistedProductIds, true);
	                                @endphp

                                <!-- start product card -->
                                <div class="bg-white border border-gray-200 shadow p-4" wire:key="buyer-product-card-{{ $product->id }}">
                                    <!-- start breadcrumb -->
                                    <div class="text-sm text-gray-600 mb-2">
                                        <a 
                                            href="{{ route('buyer.products.index', ['category' => $product->category->parent->id]) }}"
                                            class="hover:text-blue-600"
                                        >
                                            {{ $product->category->parent->getTranslation('category_name', app()->getLocale()) }}
                                        </a>
                                        >
                                        <a 
                                            href="{{ route('buyer.products.index', ['category' => $product->category->id]) }}" 
                                            class="hover:text-blue-600"
                                        >
                                            {{ $product->category->getTranslation('category_name', app()->getLocale()) }}
                                        </a>
                                    </div>
                                    <!-- end breadcrumb -->

                                    <!-- start product image -->
                                    <a href="{{ route('buyer.products.show', $product) }}">
                                        <img 
                                            src="{{ $product->imageUrl('small') }}"
                                            alt="{{ $product->name }}"
                                            loading="lazy"
                                            width="320"
                                            height="240"
                                            class="w-full h-48 object-cover rounded mb-3"
                                        >
                                    </a>
                                    <!-- end product image -->

                                    <!-- start product name -->
                                    <h3 
                                        class="font-medium text-gray-900 mb-2 truncate" 
                                        title="{{ $product->name }}"
                                    >
                                        {{ Str::limit($product->name, 200) }}
                                    </h3>
                                    <!-- end product name -->

                                    <!-- start price and stock -->
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-lg font-bold">
                                            {{ number_format($product->price, 2) }} € / {{ __('units_unit_' . strtolower($product->unit)) }}
                                        </span>
                                        <span class="text-sm text-gray-600">
                                            {{ __('product_stock') }}: {{ $product->stock }}
                                            {{ __('units_unit_' . strtolower($product->unit)) }}
                                        </span>
                                    </div>
                                    <!-- end price and stock -->

                                    <!-- start company and view -->
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="text-gray-600">
                                            {{ $product->seller->company_name }}
                                        </span>
                                        <a 
                                            href="{{ route('buyer.products.show', $product) }}"
                                            class="text-indigo-600 hover:text-indigo-800 font-medium"
                                        >
                                            {{ __('product_view') }} →
                                        </a>
                                    </div>
                                    <!-- end company and view -->

	                                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                        <x-ui.button
                                            type="button"
                                            primary
                                            outline
                                            sm
                                            icon="heart"
                                            class="w-full justify-center"
                                            spinner="addToWishlist({{ $product->id }})"
                                            wire:click="addToWishlist({{ $product->id }})"
                                            wire:loading.attr="disabled"
                                            :disabled="$isWishlisted"
                                        >
                                            {{ $isWishlisted ? __('wishlists.actions.saved') : __('wishlists.actions.add_product') }}
                                        </x-ui.button>

                                        <x-ui.button
                                            type="button"
                                            secondary
                                            outline
                                            sm
                                            icon="scale"
                                            class="w-full justify-center"
                                            spinner="addToCompare({{ $product->id }})"
                                            wire:click="addToCompare({{ $product->id }})"
                                            wire:loading.attr="disabled"
                                            :disabled="$isCompared"
                                        >
                                            {{ $isCompared ? __('compare.actions.in_list') : __('compare.actions.add') }}
                                        </x-ui.button>
                                    </div>
                                </div>
                                <!-- end product card -->
                            @endforeach
                        </div>
                        <!-- end products grid -->

                        <!-- start pagination -->
                        <div class="mt-6 flex justify-center border border-gray-200 rounded-lg p-4">
                            {{ $products->links() }}
                        </div>
                        <!-- end pagination -->
                    @endif
                </div>
                <!-- end products section -->
            </div>
            <!-- end flex container -->
        </main>
        <!-- end main -->
    </div>
    <!-- end bg container -->

    <script>
        const searchInput = document.getElementById('live-search');
        const searchResults = document.getElementById('search-results');
        let searchTimeout;

        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`/api/products/search?query=${encodeURIComponent(query)}&locale=${window.APP_LOCALE}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        const resultsDiv = document.createElement('div');
                        resultsDiv.className = 'py-2';

                        if (data.categories.length > 0) {
                            const categoriesSection = createSection('categories', data.categories);
                            resultsDiv.appendChild(categoriesSection);
                        }

                        if (data.products.length > 0) {
                            const productsSection = createSection('products', data.products);
                            resultsDiv.appendChild(productsSection);
                        }

                        if (data.categories.length === 0 && data.products.length === 0) {
                            const empty = document.createElement('div');
                            empty.className = 'px-4 py-2 text-gray-500';
                            empty.textContent = window.translations.no_results_found;
                            resultsDiv.appendChild(empty);
                        }

                        searchResults.appendChild(resultsDiv);
                        searchResults.classList.remove('hidden');
                    });
            }, 300);
        });

        function createSection(type, items) {
            const section = document.createElement('div');
            const title = type === 'categories' ? window.translations.categories : window.translations.products;

            const header = document.createElement('div');
            header.className = 'px-4 py-2 bg-gray-50 text-sm font-medium text-gray-700';
            header.textContent = title;
            section.appendChild(header);

            items.forEach(item => {
                const link = document.createElement('a');
                link.href = type === 'categories' 
                    ? `/buyer/products?category=${item.id}`
                    : `/buyer/products/${item.id}`;
                link.className = 'block px-4 py-2 hover:bg-gray-100 transition-colors';
                
                if (type === 'products') {
                    const row = document.createElement('div');
                    row.className = 'flex items-center';

                    const img = document.createElement('img');
                    img.src = item.image_url || '{{ asset((string) config('images.fallbacks.product')) }}';
                    img.className = 'w-10 h-10 object-cover rounded mr-3';
                    img.alt = item.name || '';

                    const col = document.createElement('div');

                    const name = document.createElement('div');
                    name.className = 'font-medium';
                    name.textContent = item.name || '';

                    const price = document.createElement('div');
                    price.className = 'text-sm text-gray-600';
                    price.textContent = `${item.price} €`;

                    col.appendChild(name);
                    col.appendChild(price);

                    row.appendChild(img);
                    row.appendChild(col);

                    link.appendChild(row);
                } else {
                    link.textContent = item.category_name || '';
                }
                
                section.appendChild(link);
            });

            return section;
        }

        document.addEventListener('click', function(e) {
            if (!searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.classList.add('hidden');
            }
        });

        window.APP_LOCALE = '{{ app()->getLocale() }}';
        window.translations = {
            categories: '{{ __("common_categories") }}',
            products: '{{ __("common_products") }}',
            no_results_found: '{{ __("product_no_results_found") }}'
        };
        
    </script>
</div>
