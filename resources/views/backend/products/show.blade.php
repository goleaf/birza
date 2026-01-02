<x-backend.page :title="__('common.product_details')">
    <x-slot:actions>
        <a href="{{ route('backend.products.edit', $product) }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ __('common.edit') }}
        </a>
        <a href="{{ route('backend.products.index') }}"
           class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
            {{ __('common.back') }}
        </a>
    </x-slot:actions>

    <x-ui.card>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-lg bg-gray-50 p-4">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">
                    {{ __('common.basic_information') }}
                </h3>
                <dl class="divide-y divide-gray-200">
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.name') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">{{ $product->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.category') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">
                            {{ $product->category?->getTranslation('category_name', app()->getLocale()) ?? '-' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.seller') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">
                            {{ $product->seller?->company_name ?? $product->seller?->name ?? '-' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.price') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">{{ $product->price }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.stock') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">{{ $product->stock }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.status') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">
                            @if ($product->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    {{ __('common.active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                    {{ __('common.inactive') }}
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg bg-gray-50 p-4">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">
                    {{ __('common.product_details') }}
                </h3>
                <dl class="divide-y divide-gray-200">
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.pack_type') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">{{ $product->pack_type ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.unit') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">{{ $product->unit ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.organic') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">
                            @if ($product->is_organic)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    {{ __('common.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                    {{ __('common.no') }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.min_order_price') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">{{ $product->min_order_price ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-sm font-medium text-gray-600">{{ __('common.min_order_count') }}</dt>
                        <dd class="text-sm text-gray-900 text-right">{{ $product->min_order_count ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if ($product->attributeValues->isNotEmpty())
            <div class="mt-8">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">
                    {{ __('common.product_attributes') }}
                </h3>

                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('common.attribute') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('common.value') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($product->attributeValues as $attributeValue)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $attributeValue->attribute?->getTranslation('name', app()->getLocale()) ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $attributeValue->value }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mt-8">
            <h3 class="mb-3 text-lg font-semibold text-gray-800">
                {{ __('common.description') }}
            </h3>
            <div class="prose max-w-none rounded-lg bg-gray-50 p-4">
                {!! $product->getTranslation('description', app()->getLocale()) ?? '-' !!}
            </div>
        </div>

        @if ($product->product_image || $product->product_additional_image)
            <div class="mt-8">
                <h3 class="mb-3 text-lg font-semibold text-gray-800">
                    {{ __('common.product_images') }}
                </h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @if ($product->product_image)
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                            <img
                                src="{{ Storage::url('products/' . $product->product_image) }}"
                                class="h-64 w-full object-cover"
                                alt="{{ $product->name }}"
                            >
                        </div>
                    @endif

                    @if ($product->product_additional_image)
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                            <img
                                src="{{ Storage::url('products/' . $product->product_additional_image) }}"
                                class="h-64 w-full object-cover"
                                alt="{{ $product->name }}"
                            >
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-backend.page>
