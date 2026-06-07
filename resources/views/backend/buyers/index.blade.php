<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_buyers')],
        ]"
    />

    <x-mary-header :title="__('buyers_title')" separator progress-indicator>
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
                :link="route('admin.buyers.create')"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$buyers"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 15, 25, 50]"
            striped
            show-empty-text
        >
            @scope('cell_credit_balance', $buyer)
                €{{ number_format((float) $buyer->credit_balance, 2) }}
            @endscope

            @scope('cell_verified', $buyer)
                <x-mary-badge
                    :value="$buyer->is_verified ? __('buyers_field_verified') : __('buyers_field_not_verified')"
                    class="{{ $buyer->is_verified ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('cell_active', $buyer)
                <x-mary-badge
                    :value="$buyer->is_active ? __('buyers_field_active') : __('buyers_field_inactive')"
                    class="{{ $buyer->is_active ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('actions', $buyer)
                <x-backend.action-dropdown>
                    <x-mary-menu-item
                        :title="__('common_balance')"
                        :link="route('admin.buyers.credit', $buyer)"
                        icon="o-banknotes"
                    />
                    <x-mary-menu-item
                        :title="__('common_orders')"
                        :link="route('admin.buyers.orders', $buyer)"
                        icon="o-clipboard-document-list"
                    />
                    <x-mary-menu-item
                        :title="__('common_edit')"
                        :link="route('admin.buyers.edit', $buyer)"
                        icon="o-pencil-square"
                    />
                    <x-mary-menu-separator />
                    <x-mary-menu-item
                        :title="__('common_delete')"
                        icon="o-trash"
                        class="text-error"
                        wire:click.stop="confirmDeleteBuyer({{ $buyer->id }})"
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

            <div class="grid gap-4 sm:grid-cols-2">
                <x-mary-input
                    :label="__('common_min_balance')"
                    type="number"
                    step="0.01"
                    wire:model.live="minBalance"
                    icon="o-banknotes"
                />
                <x-mary-input
                    :label="__('common_max_balance')"
                    type="number"
                    step="0.01"
                    wire:model.live="maxBalance"
                    icon="o-banknotes"
                />
            </div>

            <x-mary-select
                :label="__('buyers_field_verification_status')"
                wire:model.live="verifiedFilter"
                :options="$verificationOptions"
                option-value="id"
                option-label="name"
                icon="o-shield-check"
                :placeholder="__('buyers_field_verification_status_all')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('buyers_field_active_status')"
                wire:model.live="activeFilter"
                :options="$activeOptions"
                option-value="id"
                option-label="name"
                icon="o-check-badge"
                :placeholder="__('buyers_field_active_status_all')"
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
        reason-model="auditReason"
    />
</div>
