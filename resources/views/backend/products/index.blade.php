<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_products')],
        ]"
    />

    <x-mary-header :title="__('products_title')" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-mary-input
                :placeholder="__('common_search')"
                wire:model.live.debounce.300ms="search"
                icon="o-magnifying-glass"
                clearable
            />
        </x-slot:middle>
        <x-slot:actions>
            <x-mary-button
                :label="__('common_filter')"
                icon="o-funnel"
                responsive
                @click="$wire.drawer = true"
            />
            <x-mary-button
                :label="__('products_actions_create')"
                icon="o-plus"
                :link="route('backend.products.create')"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$products"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 15, 25, 50]"
            striped
            show-empty-text
        >
            @scope('cell_image', $product)
                <x-mary-avatar
                    :image="$product->product_image ? Storage::url('products/' . $product->product_image) : ''"
                    :alt="$product->name"
                    :placeholder="strtoupper(substr((string) $product->name, 0, 2))"
                    class="!w-14 !rounded-box"
                />
            @endscope

            @scope('cell_name', $product)
                <div class="space-y-1">
                    <div class="font-medium">{{ $product->name }}</div>
                    @if ($product->description)
                        <div class="text-xs text-base-content/60">
                            {{ \Illuminate\Support\Str::limit($product->description, 60) }}
                        </div>
                    @endif
                </div>
            @endscope

            @scope('cell_category', $product)
                {{ $product->category?->getTranslation('category_name', app()->getLocale()) ?? '-' }}
            @endscope

            @scope('cell_seller', $product)
                {{ $product->seller?->company_name ?: $product->seller?->name ?: '-' }}
            @endscope

            @scope('cell_price', $product)
                <div class="text-right font-medium">€{{ number_format((float) $product->price, 2) }}</div>
            @endscope

            @scope('cell_status', $product)
                <x-mary-badge
                    :value="$product->trashed() ? __('common_trashed') : __('common_active')"
                    class="{{ $product->trashed() ? 'badge-error badge-outline' : 'badge-success badge-outline' }}"
                />
            @endscope

            @scope('actions', $product)
                <x-backend.action-dropdown menu-class="!w-48">
                    @if ($product->trashed())
                        <x-mary-menu-item
                            :title="__('common_restore')"
                            icon="o-arrow-uturn-left"
                            class="text-success"
                            wire:click.stop="restoreProduct({{ $product->id }})"
                            spinner
                        />
                        <x-mary-menu-separator />
                        <x-mary-menu-item
                            :title="__('common_force_delete')"
                            icon="o-trash"
                            class="text-error"
                            wire:click.stop="confirmForceDeleteProduct({{ $product->id }})"
                            spinner
                        />
                    @else
                        <x-mary-menu-item
                            :title="__('common_edit')"
                            :link="route('backend.products.edit', $product)"
                            icon="o-pencil-square"
                        />
                        <x-mary-menu-separator />
                        <x-mary-menu-item
                            :title="__('common_delete')"
                            icon="o-trash"
                            class="text-error"
                            wire:click.stop="confirmDeleteProduct({{ $product->id }})"
                            spinner
                        />
                    @endif
                </x-backend.action-dropdown>
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-drawer
        wire:model="drawer"
        :title="__('common_filter')"
        right
        separator
        with-close-button
        class="w-full max-w-md"
    >
        <div class="space-y-4">
            <x-mary-input
                :label="__('common_search')"
                wire:model.live.debounce.300ms="search"
                icon="o-magnifying-glass"
                clearable
            />

            <x-mary-select
                :label="__('product_category')"
                wire:model.live="categoryFilter"
                :options="$categoryOptions"
                option-value="id"
                option-label="name"
                icon="o-tag"
                :placeholder="__('common_all')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('product_seller')"
                wire:model.live="sellerFilter"
                :options="$sellerOptions"
                option-value="id"
                option-label="name"
                icon="o-user"
                :placeholder="__('common_all')"
                placeholder-value=""
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-mary-input
                    :label="__('product_min_price')"
                    type="number"
                    step="0.01"
                    wire:model.live="minPrice"
                    prefix="€"
                />
                <x-mary-input
                    :label="__('product_max_price')"
                    type="number"
                    step="0.01"
                    wire:model.live="maxPrice"
                    prefix="€"
                />
            </div>

            <x-mary-select
                :label="__('common_status')"
                wire:model.live="statusFilter"
                :options="$statusOptions"
                option-value="id"
                option-label="name"
                icon="o-check-badge"
                :placeholder="__('common_all')"
                placeholder-value=""
            />
        </div>

        <x-slot:actions>
            <x-mary-button
                :label="__('common_reset')"
                icon="o-arrow-path"
                wire:click="clear"
                spinner
            />
        </x-slot:actions>
    </x-mary-drawer>

    <x-backend.confirm-modal
        wire:model="confirmModal"
        :title="$confirmModalTitle"
        :description="$confirmModalDescription"
        :confirm-label="$confirmModalAcceptLabel"
    />
</div>
