@php($isEditing = isset($product) && $product->exists)
<div class="space-y-6">
    <x-mary-header
        :title="$isEditing ? __('backend_products_edit_title') : __('backend_products_create_title')"
        :subtitle="$isEditing ? $name : __('products_title')"
        separator
        progress-indicator
    />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <x-mary-form wire:submit="save" enctype="multipart/form-data" class="gap-6">
            <x-mary-card :title="__('product_name')" :subtitle="__('backend_products_fields_category')" shadow>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-mary-input :label="__('product_name')" wire:model="name" required />

                    <x-mary-choices-offline
                        :label="__('product_seller')"
                        wire:model="seller_id"
                        :options="$sellerOptions"
                        option-value="id"
                        option-label="name"
                        option-sub-label="sub_label"
                        icon="o-user"
                        :placeholder="__('common_select_option')"
                        single
                        searchable
                        required
                    />

                    <x-mary-choices-offline
                        :label="__('backend_products_fields_category')"
                        wire:model.live="category_id"
                        :options="$categoryOptions"
                        option-value="id"
                        option-label="name"
                        icon="o-tag"
                        :placeholder="__('common_select_option')"
                        single
                        searchable
                        required
                    />

                    <x-mary-choices-offline
                        :label="__('backend_products_fields_country_of_origin')"
                        wire:model="country_of_origin"
                        :options="$countryOptions"
                        option-value="id"
                        option-label="name"
                        option-sub-label="sub_label"
                        icon="o-globe-europe-africa"
                        :placeholder="__('common_select_country')"
                        single
                        searchable
                        required
                    />

                    <x-mary-input
                        :label="__('backend_products_fields_price')"
                        wire:model="price"
                        type="number"
                        step="0.01"
                        prefix="€"
                        required
                    />

                    <x-mary-input
                        :label="__('backend_products_fields_pack_type')"
                        wire:model="pack_type"
                        required
                    />

                    <div class="md:col-span-2">
                        <x-mary-radio
                            :label="__('backend_products_fields_unit')"
                            wire:model="unit"
                            :options="$unitOptions"
                            option-value="id"
                            option-label="name"
                            inline
                            required
                        />
                    </div>

                    <x-mary-input
                        :label="__('backend_products_fields_stock')"
                        wire:model="stock"
                        type="number"
                        min="0"
                        required
                    />
                </div>
            </x-mary-card>

            <x-mary-card :title="__('backend_products_fields_description')" :subtitle="__('backend_products_fields_pack_type')" shadow>
                <x-mary-textarea
                    :label="__('backend_products_fields_description')"
                    :hint="__('backend_products_description_hint')"
                    :placeholder="__('backend_products_description_placeholder')"
                    wire:model="description"
                    rows="7"
                    required
                />
            </x-mary-card>

            <x-mary-card :title="__('backend_products_fields_min_order_price')" :subtitle="__('backend_products_fields_is_active')" shadow>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-mary-input
                        :label="__('backend_products_fields_min_order_price')"
                        wire:model="min_order_price"
                        type="number"
                        step="0.01"
                        prefix="€"
                    />

                    <x-mary-input
                        :label="__('backend_products_fields_min_order_count')"
                        wire:model="min_order_count"
                        type="number"
                        min="1"
                        required
                    />

                    <x-mary-input
                        :label="__('backend_products_fields_package_weight')"
                        wire:model="package_weight"
                        type="number"
                        step="0.001"
                    />

                    <x-mary-input
                        :label="__('backend_products_fields_price_per_liter')"
                        wire:model="price_per_liter"
                        type="number"
                        step="0.01"
                        prefix="€"
                    />

                    <div class="space-y-3 pt-2 md:col-span-2">
                        <x-mary-toggle
                            :label="__('backend_products_fields_is_organic')"
                            wire:model="is_organic"
                            right
                        />

                        <x-mary-toggle
                            :label="__('backend_products_fields_is_active')"
                            wire:model="is_active"
                            right
                        />
                    </div>
                </div>
            </x-mary-card>

            <x-mary-card :title="__('product_temperature_conditions')" :subtitle="__('product_use_until')" shadow>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-3">
                        <x-mary-input
                            :label="__('product_from')"
                            wire:model.live.change="temperature_conditions_from"
                            type="number"
                            suffix="°C"
                        />

                        <div class="flex items-center justify-between gap-3 text-xs text-base-content/60">
                            <span>{{ __('backend_products_temperature_range_hint') }}</span>
                            <x-mary-badge
                                :value="$temperature_conditions_from !== null ? $temperature_conditions_from . '°C' : __('common_not_specified')"
                                class="badge-primary badge-outline"
                            />
                        </div>

                        <x-mary-range
                            wire:model.live.change="temperature_conditions_from"
                            min="-40"
                            max="40"
                            step="1"
                            class="range-primary range-sm"
                            omit-error
                        />
                    </div>

                    <div class="space-y-3">
                        <x-mary-input
                            :label="__('product_to')"
                            wire:model.live.change="temperature_conditions_to"
                            type="number"
                            suffix="°C"
                        />

                        <div class="flex items-center justify-between gap-3 text-xs text-base-content/60">
                            <span>{{ __('backend_products_temperature_range_hint') }}</span>
                            <x-mary-badge
                                :value="$temperature_conditions_to !== null ? $temperature_conditions_to . '°C' : __('common_not_specified')"
                                class="badge-secondary badge-outline"
                            />
                        </div>

                        <x-mary-range
                            wire:model.live.change="temperature_conditions_to"
                            min="-40"
                            max="40"
                            step="1"
                            class="range-secondary range-sm"
                            omit-error
                        />
                    </div>

                    <x-mary-datetime
                        :label="__('product_use_until')"
                        wire:model="use_until"
                        icon="o-calendar-days"
                    />

                    <div class="space-y-3">
                        <x-mary-input
                            :label="__('product_total_shelf_life')"
                            wire:model.live.change="total_shelf_life"
                            type="number"
                            min="0"
                            suffix="d"
                            required
                        />

                        <div class="flex items-center justify-between gap-3 text-xs text-base-content/60">
                            <span>{{ __('backend_products_shelf_life_range_hint') }}</span>
                            <x-mary-badge
                                :value="$total_shelf_life !== null ? $total_shelf_life . 'd' : __('common_not_specified')"
                                class="badge-accent badge-outline"
                            />
                        </div>

                        <x-mary-range
                            wire:model.live.change="total_shelf_life"
                            min="0"
                            max="365"
                            step="1"
                            class="range-accent range-sm"
                            omit-error
                        />
                    </div>
                </div>
            </x-mary-card>

            <x-slot:actions>
                <x-mary-button
                    :label="__('backend_common_cancel')"
                    :link="route('backend.products.index')"
                />
                <x-mary-button
                    :label="$isEditing ? __('backend_common_update') : __('backend_common_create')"
                    icon="o-paper-airplane"
                    spinner="save"
                    type="submit"
                    class="btn-primary"
                />
            </x-slot:actions>
        </x-mary-form>

        <div class="space-y-6">
            <x-mary-card :title="__('common_product_images')" :subtitle="__('backend_products_image_library_hint')" shadow>
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
                />
            </x-mary-card>

            @if ($productAttributes->isNotEmpty())
                <x-mary-card :title="__('product_attributes')" :subtitle="__('backend_products_fields_category')" shadow>
                    <div class="space-y-4">
                        @foreach ($productAttributes as $attribute)
                            <x-mary-choices-offline
                                :label="$attribute->getTranslation('name', app()->getLocale())"
                                wire:model="attributeSelections.{{ $attribute->id }}"
                                :options="$attribute->values->map(fn ($value) => [
                                        'id' => $value->id,
                                        'name' => $value->getTranslation('value', app()->getLocale()),
                                    ])
                                    ->values()
                                    ->all()"
                                option-value="id"
                                option-label="name"
                                icon="o-adjustments-horizontal"
                                :placeholder="__('common_select_option')"
                                single
                                searchable
                                clearable
                            />
                        @endforeach
                    </div>
                </x-mary-card>
            @endif
        </div>
    </div>
</div>
