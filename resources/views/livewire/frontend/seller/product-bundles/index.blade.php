<div>
    <div class="space-y-6">
        <x-seller.breadcrumbs
            :items="[
                ['label' => __('bundles.title')],
            ]"
        />

        <x-ui.header :title="__('bundles.title')" :subtitle="__('bundles.seller_subtitle')">
            <x-slot:actions>
                <x-ui.button
                    href="{{ route('seller.bundles.create') }}"
                    primary
                    :label="__('bundles.create')"
                />
            </x-slot:actions>
        </x-ui.header>

        <x-ui.card class="shadow-sm sm:rounded-lg">
            @if ($bundles->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($bundles as $bundle)
                        <div wire:key="seller-bundle-{{ $bundle->id }}" class="rounded-lg border p-4 sm:p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex gap-4">
                                    <img
                                        src="{{ $bundle->imageUrl() }}"
                                        alt="{{ $bundle->name }}"
                                        class="h-20 w-20 rounded-lg object-cover"
                                        loading="lazy"
                                    >

                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-bold text-gray-900">{{ $bundle->name }}</h3>
                                            <span
                                                class="rounded-full px-2.5 py-1 text-xs font-semibold {{
                                                    match ($bundle->status) {
                                                        \App\Models\ProductBundle::STATUS_ACTIVE => 'bg-green-100 text-green-800',
                                                        \App\Models\ProductBundle::STATUS_ARCHIVED => 'bg-gray-200 text-gray-700',
                                                        \App\Models\ProductBundle::STATUS_INACTIVE => 'bg-amber-100 text-amber-800',
                                                        \App\Models\ProductBundle::STATUS_EXPIRED => 'bg-red-100 text-red-800',
                                                        default => 'bg-blue-100 text-blue-800',
                                                    }
                                                }}"
                                            >
                                                {{ $bundle->statusLabel() }}
                                            </span>
                                        </div>

                                        <div class="text-sm text-gray-600">
                                            {{ __('bundles.included_products_count', ['count' => $bundle->items_count]) }}
                                        </div>

                                        <div class="flex flex-wrap gap-4 text-sm text-gray-700">
                                            <span>{{ __('bundles.base_price') }}: {{ number_format($bundle->basePrice(), 2) }} €</span>
                                            <span>{{ __('bundles.discount') }}: {{ number_format($bundle->discountAmount(), 2) }} €</span>
                                            <span class="font-semibold">{{ __('bundles.final_price') }}: {{ number_format($bundle->finalPrice(), 2) }} €</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 lg:justify-end">
                                    <x-ui.button
                                        href="{{ route('seller.bundles.edit', $bundle) }}"
                                        sm
                                        secondary
                                        :label="__('common_edit')"
                                    />
                                    @if ($bundle->status !== \App\Models\ProductBundle::STATUS_ACTIVE)
                                        <x-ui.button
                                            type="button"
                                            sm
                                            positive
                                            wire:click="publishBundle({{ $bundle->id }})"
                                            wire:loading.attr="disabled"
                                            :label="__('bundles.publish')"
                                        />
                                    @else
                                        <x-ui.button
                                            type="button"
                                            sm
                                            secondary
                                            wire:click="unpublishBundle({{ $bundle->id }})"
                                            wire:loading.attr="disabled"
                                            :label="__('bundles.unpublish')"
                                        />
                                    @endif
                                    @if ($bundle->status !== \App\Models\ProductBundle::STATUS_ARCHIVED)
                                        <x-ui.button
                                            type="button"
                                            sm
                                            secondary
                                            wire:click="archiveBundle({{ $bundle->id }})"
                                            wire:loading.attr="disabled"
                                            :label="__('bundles.archive')"
                                        />
                                    @endif
                                    <x-ui.button
                                        type="button"
                                        sm
                                        negative
                                        wire:click="confirmDeleteBundle({{ $bundle->id }})"
                                        wire:loading.attr="disabled"
                                        :label="__('bundles.delete')"
                                    />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $bundles->links() }}
                </div>
            @else
                <div class="py-12 text-center">
                    <p class="mb-6 text-gray-600">{{ __('bundles.empty_seller') }}</p>
                    <x-ui.button href="{{ route('seller.bundles.create') }}" primary :label="__('bundles.create')" />
                </div>
            @endif
        </x-ui.card>
    </div>

    <x-backend.confirm-modal
        wire:model="confirmModal"
        :title="$confirmModalTitle"
        :description="$confirmModalDescription"
        :confirm-label="$confirmModalAcceptLabel"
    />
</div>
