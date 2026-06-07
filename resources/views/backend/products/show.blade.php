@php
    $basicDetails = [
        __('common_name') => $product->name,
        __('common_category') => $product->category?->getTranslation('category_name', app()->getLocale()) ?: __('common_not_specified'),
        __('common_seller') => $product->seller?->company_name ?: $product->seller?->name ?: __('common_not_specified'),
        __('common_price') => number_format((float) $product->price, 2) . ' €',
        __('common_stock') => $product->stock ?? __('common_not_specified'),
    ];

    $detailFields = [
        __('common_pack_type') => $product->pack_type ?: __('common_not_specified'),
        __('common_unit') => $product->unit ?: __('common_not_specified'),
        __('common_min_order_price') => $product->min_order_price ? number_format((float) $product->min_order_price, 2) . ' €' : __('common_not_specified'),
        __('common_min_order_count') => $product->min_order_count ?: __('common_not_specified'),
        __('product_temperature_conditions') => ($product->temperature_conditions_from !== null || $product->temperature_conditions_to !== null)
            ? __('product_from') . ' ' . ($product->temperature_conditions_from ?? '—') . '°C ' . __('product_to') . ' ' . ($product->temperature_conditions_to ?? '—') . '°C'
            : __('common_not_specified'),
        __('product_use_until') => $product->use_until?->format('Y-m-d') ?: __('common_not_specified'),
        __('product_total_shelf_life') => $product->total_shelf_life ?: __('common_not_specified'),
    ];

    $attributeHeaders = [
        ['key' => 'attribute_name', 'label' => __('common_attribute')],
        ['key' => 'value_text', 'label' => __('common_value')],
    ];

    $productImages = $product->imageLibraryPreview();
@endphp

<div class="space-y-6">
    <x-mary-header
        :title="__('common_product_details')"
        :subtitle="$product->name"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('backend.products.index')"
            />
            <x-mary-button
                :label="__('common_edit')"
                :link="route('backend.products.edit', $product)"
                icon="o-pencil-square"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    @if (! $product->is_active)
        <x-mary-alert
            :title="__('common_inactive')"
            :description="__('backend_products_show_inactive_alert')"
            icon="o-exclamation-triangle"
            class="alert-warning alert-soft"
            shadow
        >
            <x-slot:actions>
                <x-mary-button
                    :label="__('common_edit')"
                    :link="route('backend.products.edit', $product)"
                    class="btn-sm btn-warning btn-outline"
                />
            </x-slot:actions>
        </x-mary-alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-2">
        <x-mary-card :title="__('common_basic_information')" shadow>
            <dl class="divide-y divide-gray-200">
                @foreach ($basicDetails as $label => $value)
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-gray-600">{{ $label }}</dt>
                        <dd class="text-right text-sm text-gray-900">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-sm font-medium text-gray-600">{{ __('common_status') }}</dt>
                    <dd class="text-right">
                        <x-mary-badge
                            :value="$product->is_active ? __('common_active') : __('common_inactive')"
                            class="{{ $product->is_active ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                        />
                    </dd>
                </div>
            </dl>
        </x-mary-card>

        <x-mary-card :title="__('common_product_details')" shadow>
            <dl class="divide-y divide-gray-200">
                @foreach ($detailFields as $label => $value)
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-gray-600">{{ $label }}</dt>
                        <dd class="text-right text-sm text-gray-900">{{ $value }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-sm font-medium text-gray-600">{{ __('common_organic') }}</dt>
                    <dd class="text-right">
                        <x-mary-badge
                            :value="$product->is_organic ? __('common_yes') : __('common_no')"
                            class="{{ $product->is_organic ? 'badge-success badge-outline' : 'badge-neutral badge-outline' }}"
                        />
                    </dd>
                </div>
            </dl>
        </x-mary-card>
    </div>

    @if ($product->attributeValues->isNotEmpty())
        <x-mary-card :title="__('common_product_attributes')" shadow>
            <x-mary-table
                :headers="$attributeHeaders"
                :rows="$product->attributeValues"
                striped
                no-hover
                show-empty-text
                :empty-text="__('common_no_attributes')"
            >
                @scope('cell_attribute_name', $attributeValue)
                    {{ $attributeValue->attribute?->getTranslation('name', app()->getLocale()) ?: __('common_not_specified') }}
                @endscope

                @scope('cell_value_text', $attributeValue)
                    {{ $attributeValue->getTranslation('value', app()->getLocale()) ?: __('common_not_specified') }}
                @endscope
            </x-mary-table>
        </x-mary-card>
    @endif

    <x-mary-card :title="__('common_description')" shadow>
        @if ($description = $product->getTranslation('description', app()->getLocale()))
            <div class="prose max-w-none">
                {!! $description !!}
            </div>
        @else
            <div class="text-sm text-base-content/60">{{ __('common_not_specified') }}</div>
        @endif
    </x-mary-card>

    @if ($productImages->isNotEmpty())
        <x-mary-card :title="__('common_product_images')" shadow>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($productImages as $image)
                    <div class="overflow-hidden rounded-2xl border border-base-200 bg-base-100">
                        <img
                            src="{{ $image['url'] }}"
                            alt="{{ $product->name }}"
                            class="h-72 w-full object-cover"
                        >
                    </div>
                @endforeach
            </div>
        </x-mary-card>
    @else
        <x-mary-alert
            :title="__('common_product_images')"
            :description="__('backend_products_show_no_images_alert')"
            icon="o-exclamation-triangle"
            class="alert-info alert-outline"
            shadow
        />
    @endif
</div>
