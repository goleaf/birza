<div class="space-y-6">
    <x-mary-header
        :title="__('backend_attribute_values_index_title')"
        :subtitle="$attribute->getTranslation('name', app()->getLocale())"
        separator
        progress-indicator
    >
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
                :label="__('backend_attribute_values_actions_create')"
                icon="o-plus"
                :link="route('backend.attributes.values.create', $attribute)"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$values"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 15, 25, 50]"
            striped
            show-empty-text
        >
            @scope('cell_value', $value)
                {{ $value->getTranslation('value', app()->getLocale()) }}
            @endscope

            @scope('cell_active', $value)
                <x-mary-badge
                    :value="$value->is_active ? __('common_active') : __('common_inactive')"
                    class="{{ $value->is_active ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('actions', $value)
                <x-backend.action-dropdown menu-class="!w-44">
                    <x-mary-menu-item
                        :title="__('common_edit')"
                        :link="route('backend.attributes.values.edit', [$this->attribute, $value])"
                        icon="o-pencil-square"
                    />
                    <x-mary-menu-separator />
                    <x-mary-menu-item
                        :title="__('common_delete')"
                        icon="o-trash"
                        class="text-error"
                        wire:click.stop="confirmDeleteValue({{ $value->id }})"
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
                :label="__('common_status')"
                wire:model.live="activeFilter"
                :options="$activeOptions"
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
