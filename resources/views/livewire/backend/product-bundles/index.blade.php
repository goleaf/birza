<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('bundles.title')],
        ]"
    />

    <x-mary-header :title="__('bundles.title')" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input
                :placeholder="__('common_search')"
                wire:model.live.debounce.300ms="search"
                icon="o-magnifying-glass"
                clearable
            />
        </x-slot:middle>
    </x-mary-header>

    <x-mary-card shadow>
        <div class="mb-4 grid gap-3 md:grid-cols-3">
            <x-mary-select
                :label="__('common_status')"
                wire:model.live="statusFilter"
                :options="collect($statusOptions)->map(fn ($status) => ['id' => $status, 'name' => __('bundles.status.' . $status)])->all()"
                option-value="id"
                option-label="name"
                :placeholder="__('common_all')"
                placeholder-value=""
            />
            <x-mary-select
                :label="__('product_seller')"
                wire:model.live="sellerFilter"
                :options="$sellers->map(fn ($seller) => ['id' => (string) $seller->id, 'name' => $seller->company_name ?: $seller->name])->all()"
                option-value="id"
                option-label="name"
                :placeholder="__('common_all')"
                placeholder-value=""
            />
            <div class="flex items-end">
                <x-mary-button
                    :label="__('common_reset')"
                    icon="o-arrow-path"
                    wire:click="clearFilters"
                    spinner
                />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>{{ __('bundles.name') }}</th>
                        <th>{{ __('product_seller') }}</th>
                        <th>{{ __('common_status') }}</th>
                        <th class="text-right">{{ __('bundles.included_products') }}</th>
                        <th class="text-right">{{ __('bundles.final_price') }}</th>
                        <th class="text-right">{{ __('common_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bundles as $bundle)
                        <tr wire:key="backend-product-bundle-{{ $bundle->id }}">
                            <td>
                                <div class="font-medium">{{ $bundle->name }}</div>
                                <div class="text-xs text-base-content/60">{{ $bundle->slug }}</div>
                            </td>
                            <td>{{ $bundle->seller?->company_name ?: $bundle->seller?->name ?: '-' }}</td>
                            <td>
                                <x-mary-badge
                                    :value="$bundle->statusLabel()"
                                    class="{{
                                        match ($bundle->status) {
                                            \App\Models\ProductBundle::STATUS_ACTIVE => 'badge-success',
                                            \App\Models\ProductBundle::STATUS_ARCHIVED => 'badge-ghost',
                                            \App\Models\ProductBundle::STATUS_INACTIVE => 'badge-warning',
                                            \App\Models\ProductBundle::STATUS_EXPIRED => 'badge-error',
                                            default => 'badge-info',
                                        }
                                    }}"
                                />
                            </td>
                            <td class="text-right">{{ $bundle->items_count }}</td>
                            <td class="text-right">€{{ number_format($bundle->finalPrice(), 2) }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <x-mary-button
                                        icon="o-eye"
                                        :link="route('admin.bundles.show', $bundle)"
                                        size="sm"
                                        responsive
                                    />
                                    @if ($bundle->status !== \App\Models\ProductBundle::STATUS_ACTIVE)
                                        <x-mary-button
                                            icon="o-check-circle"
                                            class="btn-success btn-sm"
                                            wire:click="publishBundle({{ $bundle->id }})"
                                            spinner
                                            responsive
                                        />
                                    @else
                                        <x-mary-button
                                            icon="o-x-circle"
                                            class="btn-warning btn-sm"
                                            wire:click="unpublishBundle({{ $bundle->id }})"
                                            spinner
                                            responsive
                                        />
                                    @endif
                                    @if ($bundle->status !== \App\Models\ProductBundle::STATUS_ARCHIVED)
                                        <x-mary-button
                                            icon="o-archive-box"
                                            class="btn-ghost btn-sm"
                                            wire:click="archiveBundle({{ $bundle->id }})"
                                            spinner
                                            responsive
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-base-content/60">
                                {{ __('bundles.empty_admin') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bundles->links() }}
        </div>
    </x-mary-card>
</div>
