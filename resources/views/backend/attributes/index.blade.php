<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_attributes')],
        ]"
    />

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-mary-stat :title="__('backend_attributes_stats_total')" :value="$stats['total']" icon="o-squares-2x2" color="text-primary" />
        <x-mary-stat :title="__('backend_attributes_stats_active')" :value="$stats['active']" icon="o-check-badge" color="text-success" />
        <x-mary-stat :title="__('backend_attributes_stats_filterable')" :value="$stats['filterable']" icon="o-funnel" color="text-info" />
        <x-mary-stat :title="__('backend_attributes_stats_required')" :value="$stats['required']" icon="o-exclamation-circle" color="text-warning" />
    </div>

    <x-mary-header :title="__('backend_attributes_title')" separator progress-indicator>
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
                :label="__('backend_attributes_actions_create')"
                icon="o-plus"
                :link="route('backend.attributes.create')"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$attributeRecords"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 20, 50]"
            striped
            show-empty-text
        >
            @scope('cell_name', $attribute)
                {{ $attribute->getTranslation('name', app()->getLocale()) }}
            @endscope

            @scope('cell_type', $attribute)
                {{ __('backend_attributes_types_' . $attribute->type) }}
            @endscope

            @scope('cell_active', $attribute)
                <x-mary-badge
                    :value="$attribute->is_active ? __('common_active') : __('common_inactive')"
                    class="{{ $attribute->is_active ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('cell_filterable', $attribute)
                <x-mary-badge
                    :value="$attribute->is_filterable ? __('common_yes') : __('common_no')"
                    class="{{ $attribute->is_filterable ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('cell_required', $attribute)
                <x-mary-badge
                    :value="$attribute->is_required ? __('common_yes') : __('common_no')"
                    class="{{ $attribute->is_required ? 'badge-success badge-outline' : 'badge-error badge-outline' }}"
                />
            @endscope

            @scope('actions', $attribute)
                <x-backend.action-dropdown>
                    <x-mary-menu-item
                        :title="__('backend_attributes_actions_add_value')"
                        :link="route('backend.attributes.values.create', $attribute)"
                        icon="o-plus"
                    />
                    <x-mary-menu-item
                        :title="__('common_edit')"
                        :link="route('backend.attributes.edit', $attribute)"
                        icon="o-pencil-square"
                    />
                    <x-mary-menu-separator />
                    <x-mary-menu-item
                        :title="__('common_delete')"
                        icon="o-trash"
                        class="text-error"
                        wire:click.stop="confirmDeleteAttribute({{ $attribute->id }})"
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
                wire:model.live="statusFilter"
                :options="$statusOptions"
                option-value="id"
                option-label="name"
                icon="o-check-badge"
                :placeholder="__('backend_attributes_filters_all_statuses')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('backend_attributes_fields_type')"
                wire:model.live="typeFilter"
                :options="$typeOptions"
                option-value="id"
                option-label="name"
                icon="o-queue-list"
                :placeholder="__('backend_attributes_filters_all_types')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('backend_attributes_fields_is_filterable')"
                wire:model.live="filterableFilter"
                :options="$filterableOptions"
                option-value="id"
                option-label="name"
                icon="o-funnel"
                :placeholder="__('backend_attributes_fields_is_filterable')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('backend_attributes_fields_is_required')"
                wire:model.live="requiredFilter"
                :options="$requiredOptions"
                option-value="id"
                option-label="name"
                icon="o-exclamation-circle"
                :placeholder="__('backend_attributes_fields_is_required')"
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
