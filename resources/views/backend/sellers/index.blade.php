<div class="space-y-6">
    <x-mary-header :title="__('sellers_title')" separator progress-indicator>
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
                :label="__('common_create')"
                icon="o-plus"
                :link="route('backend.sellers.create')"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$sellers"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 15, 25, 50]"
            striped
            show-empty-text
        >
            @scope('cell_active', $seller)
                <x-mary-badge
                    :value="$seller->is_active ? __('sellers_field_active') : __('sellers_field_inactive')"
                    class="{{ $seller->is_active ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('actions', $seller)
                <x-backend.action-dropdown>
                    <x-mary-menu-item
                        :title="__('common_view')"
                        :link="route('backend.sellers.show', $seller)"
                        icon="o-eye"
                    />
                    <x-mary-menu-item
                        :title="__('common_edit')"
                        :link="route('backend.sellers.edit', $seller)"
                        icon="o-pencil-square"
                    />
                    <x-mary-menu-item
                        :title="__('sellers_orders_list')"
                        :link="route('backend.sellers.orders', $seller)"
                        icon="o-clipboard-document-list"
                    />
                    <x-mary-menu-separator />
                    <x-mary-menu-item
                        :title="__('common_delete')"
                        icon="o-trash"
                        class="text-error"
                        wire:click.stop="confirmDeleteSeller({{ $seller->id }})"
                        spinner
                    />
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
                :label="__('sellers_field_active_status')"
                wire:model.live="activeFilter"
                :options="$activeOptions"
                option-value="id"
                option-label="name"
                icon="o-check-badge"
                :placeholder="__('sellers_field_active_status_all')"
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
