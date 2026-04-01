<div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
    <div class="rounded-lg bg-white shadow-sm">
        <div class="border-b border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ isset($product) ? __('backend_products_edit_title') : __('backend_products_create_title') }}
                </h2>
            </div>
        </div>

        <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-6 p-6">
            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_category') }}
                        </label>
                        <select
                            id="category_id"
                            wire:model="category_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">{{ __('common_select_option') }}</option>
                            @foreach ($categories->whereNull('parent_category_id') as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->getTranslation('category_name', app()->getLocale()) }}
                                </option>
                                @foreach ($cat->subcategories()->orderBy('category_name->en')->get() as $subcategory)
                                    <option value="{{ $subcategory->id }}">
                                        -- {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="price" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_price') }}
                        </label>
                        <input type="number" step="0.01" id="price" wire:model="price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="pack_type" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_pack_type') }}
                        </label>
                        <input type="text" id="pack_type" wire:model="pack_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="unit" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_unit') }}
                        </label>
                        <select id="unit" wire:model="unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (App\Models\Product::UNITS as $unitOption)
                                <option value="{{ $unitOption }}">{{ __('units_unit_' . strtolower($unitOption)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="country_of_origin" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_country_of_origin') }}
                        </label>
                        <select id="country_of_origin" wire:model="country_of_origin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('common_select_country') }}</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">
                                    {{ $country->getTranslation('country_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="is_organic" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_is_organic') }}
                        </label>
                        <select id="is_organic" wire:model="is_organic" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">{{ __('common_no') }}</option>
                            <option value="1">{{ __('common_yes') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="is_active" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_is_active') }}
                        </label>
                        <select id="is_active" wire:model="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="1">{{ __('product_active') }}</option>
                            <option value="0">{{ __('product_inactive') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="stock" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_stock') }}
                        </label>
                        <input type="number" id="stock" wire:model="stock" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="min_order_price" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_min_order_price') }}
                        </label>
                        <input type="number" step="0.01" id="min_order_price" wire:model="min_order_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="min_order_count" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_min_order_count') }}
                        </label>
                        <input type="number" id="min_order_count" wire:model="min_order_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="package_weight" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_package_weight') }}
                        </label>
                        <input type="number" step="0.001" id="package_weight" wire:model="package_weight" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="price_per_liter" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_price_per_liter') }}
                        </label>
                        <input type="number" step="0.01" id="price_per_liter" wire:model="price_per_liter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <label for="description" class="mb-1 block text-sm font-medium text-gray-700">
                    {{ __('backend_products_fields_description') }}
                </label>
                <textarea id="description" wire:model="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="product_image" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_product_image') }}
                        </label>
                        @if (isset($product) && $product->product_image)
                            <img src="{{ Storage::url('products/' . $product->product_image) }}" alt="{{ __('backend_products_fields_product_image') }}" class="mb-2 w-32 rounded">
                        @endif
                        <input type="file" id="product_image" wire:model="product_image" class="mt-1 block w-full">
                    </div>

                    <div>
                        <label for="product_additional_image" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend_products_fields_product_additional_image') }}
                        </label>
                        @if (isset($product) && $product->product_additional_image)
                            <img src="{{ Storage::url('products/' . $product->product_additional_image) }}" alt="{{ __('backend_products_fields_product_additional_image') }}" class="mb-2 w-32 rounded">
                        @endif
                        <input type="file" id="product_additional_image" wire:model="product_additional_image" class="mt-1 block w-full">
                    </div>
                </div>
            </div>

            @if ($productAttributes && $productAttributes->count() > 0)
                <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('product_attributes') }}</h3>
                    <div class="space-y-6">
                        @foreach ($productAttributes as $attribute)
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">
                                    {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                </label>
                                <select wire:model="attributeSelections.{{ $attribute->id }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ __('common_select_option') }}</option>
                                    @foreach ($attribute->values as $value)
                                        <option value="{{ $value->id }}">
                                            {{ $value->getTranslation('value', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end space-x-3">
                <a href="{{ route('backend.products.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    {{ __('backend_common_cancel') }}
                </a>
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                    {{ isset($product) ? __('backend_common_update') : __('backend_common_create') }}
                </button>
            </div>
        </form>
    </div>
</div>
