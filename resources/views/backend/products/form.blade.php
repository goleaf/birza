<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ isset($product) ? __('backend_products_edit_title') : __('backend_products_create_title') }}
                    </h2>
                </div>

                <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-6">

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_category') }}
                                </label>
                                <select id="category_id" wire:model.defer="category_id" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror">
                                    @foreach ($categories->whereNull('parent_category_id') as $cat)
                                        <option value="{{ $cat->id }}">
                                            {{ $cat->getTranslation('category_name', app()->getLocale()) }}
                                        </option>
                                        @foreach ($cat->subcategories()->orderBy('category_name')->get() as $subcategory)
                                            <option value="{{ $subcategory->id }}">
                                                --
                                                {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_price') }} *
                                </label>
                                <input type="number" step="0.01" id="price" wire:model.defer="price"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('price') border-red-500 @enderror"
                                       required>
                                @error('price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_pack_type') }} *
                                </label>
                                <input type="text" id="pack_type" wire:model.defer="pack_type" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_unit') }} *
                                </label>
                                <select id="unit" wire:model.defer="unit" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    @foreach (App\Models\Product::UNITS as $unit)
                                        <option value="{{ $unit }}">
                                            {{ __('units_unit_' . strtolower($unit)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_country_of_origin') }} *
                                </label>
                                <select id="country_of_origin" wire:model.defer="country_of_origin" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">{{ __('common_select_country') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">
                                            {{ $country->getTranslation('country_name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_is_organic') }} *
                                </label>
                                <select id="is_organic" wire:model.defer="is_organic" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="0">{{ __('common_no') }}</option>
                                    <option value="1">
                                        {{ __('common_yes') }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_is_active') }} *
                                </label>
                                <select id="is_active" wire:model.defer="is_active" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="1">{{ __('product_active') }}</option>
                                    <option value="0">
                                        {{ __('product_inactive') }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_stock') }} *
                                </label>
                                <input type="number" id="stock" wire:model.defer="stock" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="min_order_price" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_min_order_price') }}
                                </label>
                                <input type="number" step="0.01" id="min_order_price"
                                       wire:model.defer="min_order_price"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_order_price') border-red-500 @enderror">
                                @error('min_order_price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="min_order_count" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_min_order_count') }}
                                </label>
                                <input type="number" id="min_order_count" wire:model.defer="min_order_count"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_order_count') border-red-500 @enderror">
                                @error('min_order_count')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="package_weight" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_package_weight') }}
                                </label>
                                <input type="number" step="0.003" id="package_weight"
                                       wire:model.defer="package_weight"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('package_weight') border-red-500 @enderror">
                                @error('package_weight')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="price_per_liter" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_price_per_liter') }}
                                </label>
                                <input type="number" step="0.01" id="price_per_liter"
                                       wire:model.defer="price_per_liter"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('price_per_liter') border-red-500 @enderror">
                                @error('price_per_liter')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_description') }}
                                </label>
                                <textarea id="description" wire:model.defer="description" required rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="product_image" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_product_image') }}
                                </label>
                                @if (isset($product) && $product->product_image)
                                    <img src="{{ Storage::url('products/' . $product->product_image) }}"
                                         alt="{{ __('backend_products_fields_product_image') }}" class="mb-2 w-32 rounded">
                                @endif
                                <input type="file" id="product_image" wire:model="product_image"
                                       {{ isset($product) ? '' : 'required' }}
                                       class="mt-1 block w-full @error('product_image') border-red-500 @enderror">
                                @error('product_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="product_additional_image"
                                       class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('backend_products_fields_product_additional_image') }}
                                </label>
                                @if (isset($product) && $product->product_additional_image)
                                    <img src="{{ Storage::url('products/' . $product->product_additional_image) }}"
                                         alt="{{ __('backend_products_fields_product_additional_image') }}" class="mb-2 w-32 rounded">
                                @endif
                                <input type="file" id="product_additional_image"
                                       wire:model="product_additional_image"
                                       class="mt-1 block w-full @error('product_additional_image') border-red-500 @enderror">
                                @error('product_additional_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    @if ($attributes && $attributes->count() > 0)
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('product_attributes') }}</h3>
                            <div class="space-y-6">
                                @foreach ($attributes as $attribute)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                        </label>

                                        <select wire:model.defer="attributeSelections.{{ $attribute->id }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('attributeSelections.' . $attribute->id) border-red-500 @enderror"
                                                {{ $attribute->is_required ? 'required' : '' }}>
                                            <option value="">{{ __('common_select_option') }}</option>
                                            @foreach ($attribute->values as $value)
                                                <option value="{{ $value->id }}">
                                                    {{ $value->getTranslation('value', app()->getLocale()) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('attributeSelections.' . $attribute->id)
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('backend.products.index') }}"
                           class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('backend_common_cancel') }}
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ isset($product) ? __('backend_common_update') : __('backend_common_create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
