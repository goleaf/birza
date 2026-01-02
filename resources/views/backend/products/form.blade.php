<x-backend.page :title="isset($product) ? __('backend.products.edit.title') : __('backend.products.create.title')">
    <x-ui.card>
        <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-6">
            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.category') }}
                        </label>
                        <select
                            id="category_id"
                            wire:model.defer="category_id"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror"
                        >
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
                        <label for="price" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.price') }} *
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            id="price"
                            wire:model.defer="price"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('price') border-red-500 @enderror"
                            required
                        >
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.pack_type') }} *
                        </label>
                        <input
                            type="text"
                            id="pack_type"
                            wire:model.defer="pack_type"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.unit') }} *
                        </label>
                        <select id="unit" wire:model.defer="unit" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach (App\Models\Product::UNITS as $unit)
                                <option value="{{ $unit }}">
                                    {{ __("units.$unit") }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.country_of_origin') }} *
                        </label>
                        <select id="country_of_origin" wire:model.defer="country_of_origin" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">{{ __('common.select_country') }}</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">
                                    {{ $country->getTranslation('country_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.is_organic') }} *
                        </label>
                        <select id="is_organic" wire:model.defer="is_organic" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="0">{{ __('common.no') }}</option>
                            <option value="1">{{ __('common.yes') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.is_active') }} *
                        </label>
                        <select id="is_active" wire:model.defer="is_active" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="1">{{ __('product.active') }}</option>
                            <option value="0">{{ __('product.inactive') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.stock') }} *
                        </label>
                        <input type="number" id="stock" wire:model.defer="stock" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="min_order_price" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.min_order_price') }}
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            id="min_order_price"
                            wire:model.defer="min_order_price"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_order_price') border-red-500 @enderror"
                        >
                        @error('min_order_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="min_order_count" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.min_order_count') }}
                        </label>
                        <input
                            type="number"
                            id="min_order_count"
                            wire:model.defer="min_order_count"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('min_order_count') border-red-500 @enderror"
                        >
                        @error('min_order_count')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="package_weight" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.package_weight') }}
                        </label>
                        <input
                            type="number"
                            step="0.003"
                            id="package_weight"
                            wire:model.defer="package_weight"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('package_weight') border-red-500 @enderror"
                        >
                        @error('package_weight')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price_per_liter" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.price_per_liter') }}
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            id="price_per_liter"
                            wire:model.defer="price_per_liter"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('price_per_liter') border-red-500 @enderror"
                        >
                        @error('price_per_liter')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="description" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.description') }}
                        </label>
                        <textarea
                            id="description"
                            wire:model.defer="description"
                            required
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                        ></textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="product_image" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.product_image') }}
                        </label>
                        @if (isset($product) && $product->product_image)
                            <img
                                src="{{ Storage::url('products/' . $product->product_image) }}"
                                alt="{{ __('backend.products.fields.product_image') }}"
                                class="mb-2 w-32 rounded"
                            >
                        @endif
                        <input
                            type="file"
                            id="product_image"
                            wire:model="product_image"
                            {{ isset($product) ? '' : 'required' }}
                            class="mt-1 block w-full @error('product_image') border-red-500 @enderror"
                        >
                        @error('product_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="product_additional_image" class="mb-1 block text-sm font-medium text-gray-700">
                            {{ __('backend.products.fields.product_additional_image') }}
                        </label>
                        @if (isset($product) && $product->product_additional_image)
                            <img
                                src="{{ Storage::url('products/' . $product->product_additional_image) }}"
                                alt="{{ __('backend.products.fields.product_additional_image') }}"
                                class="mb-2 w-32 rounded"
                            >
                        @endif
                        <input
                            type="file"
                            id="product_additional_image"
                            wire:model="product_additional_image"
                            class="mt-1 block w-full @error('product_additional_image') border-red-500 @enderror"
                        >
                        @error('product_additional_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            @if ($attributes && $attributes->count() > 0)
                <div class="rounded-lg bg-gray-50 p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('product.attributes') }}</h3>
                    <div class="space-y-6">
                        @foreach ($attributes as $attribute)
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">
                                    {{ $attribute->getTranslation('name', app()->getLocale()) }}
                                </label>

                                <select
                                    wire:model.defer="attributeSelections.{{ $attribute->id }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('attributeSelections.' . $attribute->id) border-red-500 @enderror"
                                    {{ $attribute->is_required ? 'required' : '' }}
                                >
                                    <option value="">{{ __('common.select_option') }}</option>
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

            <x-ui.form-actions
                :submit-label="isset($product) ? __('backend.common.update') : __('backend.common.create')"
                :cancel-href="route('backend.products.index')"
                submit-target="save"
            />
        </form>
    </x-ui.card>
</x-backend.page>
