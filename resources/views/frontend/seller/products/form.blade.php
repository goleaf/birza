@php
    $isEditing = isset($product->id);
@endphp

<div>
        <!-- start main container -->
        <div class="container mx-auto px-4 py-8">
            <x-seller.breadcrumbs
                class="mb-6"
                :items="[
                    ['label' => __('common_products'), 'link' => route('seller.products.index')],
                    ['label' => $isEditing ? $product->name : __('common_create')],
                ]"
            />

            <x-ui.header
                class="mb-6"
                :title="$isEditing ? __('product_edit_product') : __('product_create_new_product')"
                :subtitle="$isEditing ? $product->name : __('product_products_list')"
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('seller.products.index')"
                        secondary
                        :label="__('common_back_to_products')"
                    />
                </x-slot:actions>
            </x-ui.header>

            <!-- start form container -->
            <x-ui.card class="rounded-lg shadow-lg">
                <!-- start form -->
                <form wire:submit.prevent="save" enctype="multipart/form-data">
                    <input type="hidden" wire:model="category_id">

                    <!-- start name field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_name') }} *
                        </label>
                        <input 
                            type="text"
                            required 
                            wire:model="name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('name') ? 'border-red-500' : '' }}"
                        >
                        @error('name')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end name field -->

                    
                    <!-- start price field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_price') }} * <span class="text-gray-500 font-normal">({{ __('product_price_without_vat') }})</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            required 
                            wire:model="price"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('price') ? 'border-red-500' : '' }}"
                        >
                        @error('price')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end price field -->

                    <!-- start pack type field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_pack_type') }} *
                        </label>
                        <input 
                            type="text"
                            wire:model="pack_type"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('pack_type') ? 'border-red-500' : '' }}"
                        >
                        @error('pack_type')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end pack type field -->

                    <!-- start unit field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_unit') }} *
                        </label>
                        <select 
                            required 
                            wire:model="unit"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('unit') ? 'border-red-500' : '' }}"
                        >
                            @foreach ($unitOptions as $unitOption)
                                <option 
                                    value="{{ $unitOption['id'] }}" 
                                >
                                    {{ $unitOption['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end unit field -->

                    <!-- start country field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_country_of_origin') }} *
                        </label>
                        <select 
                            required 
                            wire:model="country_of_origin"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('country_of_origin') ? 'border-red-500' : '' }}"
                        >
                            <option value="">
                                {{ __('common_select_country') }}
                            </option>
                            @foreach ($countries as $country)
                                <option 
                                    value="{{ $country->id }}" 
                                >
                                    {{ $country->getTranslation('country_name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_of_origin')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end country field -->

                    <!-- start organic field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_is_organic') }} *
                        </label>
                        <select 
                            required 
                            wire:model="is_organic"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('is_organic') ? 'border-red-500' : '' }}"
                        >
                            <option 
                                value="0" 
                            >
                                {{ __('common_no') }}
                            </option>
                            <option 
                                value="1" 
                            >
                                {{ __('common_yes') }}
                            </option>
                        </select>
                        @error('is_organic')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end organic field -->

                    <!-- start active field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_is_active') }} *
                        </label>
                        <select 
                            required 
                            wire:model="is_active"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('is_active') ? 'border-red-500' : '' }}"
                        >
                            <option 
                                value="0" 
                            >
                                {{ __('common_no') }}
                            </option>
                            <option 
                                value="1" 
                            >
                                {{ __('common_yes') }}
                            </option>
                        </select>
                        @error('is_active')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end active field -->

                    <!-- start min order price field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_min_order_price') }}
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            wire:model="min_order_price"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('min_order_price') ? 'border-red-500' : '' }}"
                        >
                        @error('min_order_price')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end min order price field -->

                    <!-- start min order count field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_min_order_count') }} *
                        </label>
                        <input 
                            type="number" 
                            wire:model="min_order_count"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('min_order_count') ? 'border-red-500' : '' }}"
                            required
                        >
                        @error('min_order_count')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end min order count field -->

                    <!-- start stock field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_stock') }} *
                        </label>
                        <input 
                            type="number" 
                            required 
                            wire:model="stock"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('stock') ? 'border-red-500' : '' }}"
                        >
                        @error('stock')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end stock field -->

                    <!-- start description field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_description') }} *
                        </label>
                        @foreach (config('app.locales') as $locale)
                            <div class="mb-4">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ strtoupper($locale) }}{{ $locale == app()->getLocale() ? ' *' : '' }}
                                </label>
                                @php
                                    $markdownModel = 'description.' . $locale;
                                    $markdownIsRequired = $locale == app()->getLocale();
                                    $markdownHasError = $errors->has($markdownModel);
                                    $markdownConfig = [
                                        'toolbar' => [
                                            'heading',
                                            'bold',
                                            'italic',
                                            '|',
                                            'quote',
                                            'unordered-list',
                                            'ordered-list',
                                            '|',
                                            'link',
                                            'upload-image',
                                            '|',
                                            'preview',
                                            'side-by-side',
                                        ],
                                        'minHeight' => '180px',
                                    ];
                                    $markdownEditorClass = $markdownHasError
                                        ? '[&_.editor-toolbar]:!border-red-500 [&_.CodeMirror]:!border-red-500'
                                        : '';
                                @endphp
                                <x-ui.markdown-editor
                                    wire:model="{{ $markdownModel }}"
                                    :label="null"
                                    :hint="null"
                                    class="{{ $markdownEditorClass }}"
                                    :required="$markdownIsRequired"
                                    folder="markdown/products"
                                    :config="$markdownConfig"
                                />
                                @error($markdownModel)
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                    <!-- end description field -->





                    <!-- start temperature conditions fields -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_temperature_conditions') }}
                        </label>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ __('product_from') }}
                                </label>
                                <input
                                    type="number"
                                    wire:model="temperature_conditions_from"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('temperature_conditions_from') ? 'border-red-500' : '' }}"
                                >
                                @error('temperature_conditions_from')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-gray-600 text-xs mb-1">
                                    {{ __('product_to') }}
                                </label>
                                <input
                                    type="number"
                                    wire:model="temperature_conditions_to"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('temperature_conditions_to') ? 'border-red-500' : '' }}"
                                >
                                @error('temperature_conditions_to')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- end temperature conditions fields -->

                    <!-- start use until field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_use_until') }}
                        </label>
                        <x-ui.datepicker
                            wire:model="use_until"
                            class="w-full {{ $errors->has('use_until') ? '[&_label]:!border-red-500' : '' }}"
                            :label="null"
                            clearable
                        />
                        @error('use_until')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end use until field -->



                    <!-- start total shelf life field -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('product_total_shelf_life') }} *
                        </label>
                        <input
                            type="number"
                            wire:model="total_shelf_life"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ $errors->has('total_shelf_life') ? 'border-red-500' : '' }}"
                            required
                        >
                        @error('total_shelf_life')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- end total shelf life field -->



                    <!-- start images field -->
                    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4">
                        <x-mary-image-library
                            :label="__('common_product_images')"
                            :hint="__('backend_products_image_library_hint')"
                            wire:model="imageFiles"
                            wire:library="imageLibrary"
                            :preview="$imageLibrary"
                            accept="image/png, image/jpeg, image/webp"
                            :change-text="__('common_edit')"
                            :crop-text="__('common_crop')"
                            :remove-text="__('common_remove')"
                            :crop-title-text="__('common_crop_image')"
                            :crop-cancel-text="__('backend_common_cancel')"
                            :crop-save-text="__('common_crop')"
                            :add-files-text="__('common_add_images')"
                            required
                        />
                    </div>
                    <!-- end images field -->

                    <!-- start form buttons -->
                    <div class="flex items-center justify-between">
                        <x-ui.button
                            type="submit"
                            primary
                            spinner="save"
                            wire:loading.attr="disabled"
                            :label="$isEditing ? __('product_update') : __('product_create')"
                        />
                        <x-ui.button
                            :href="route('seller.products.index')"
                            secondary
                            :label="__('common_cancel')"
                        />
                    </div>
                    <!-- end form buttons -->
                </form>
                <!-- end form -->
            </x-ui.card>
            <!-- end form container -->
        </div>
        <!-- end main container -->
</div>
    <!-- end section -->
