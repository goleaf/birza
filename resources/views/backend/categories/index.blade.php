<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_categories')],
        ]"
    />

    <x-mary-header :title="__('backend_categories_title')" separator progress-indicator>
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
                :label="__('backend_categories_actions_create')"
                icon="o-plus"
                :link="route('admin.categories.create')"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="[
                ['key' => 'name', 'label' => __('backend_categories_fields_name'), 'sortable' => false],
                ['key' => 'parent_name', 'label' => __('backend_categories_fields_parent_category'), 'sortable' => false],
                ['key' => 'attributes', 'label' => __('backend_categories_fields_attributes'), 'sortable' => false],
            ]"
            :rows="$categories"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 15, 25, 50]"
            striped
            show-empty-text
        >
            @scope('cell_name', $category)
                <div class="space-y-1">
                    <div class="font-medium">{{ $category->getTranslation('category_name', app()->getLocale()) }}</div>
                    @if ($category->parent_category_id)
                        <div class="text-xs text-base-content/60">
                            {{ __('seller_subcategories') }}
                        </div>
                    @endif
                </div>
            @endscope

            @scope('cell_parent_name', $category)
                {{ $category->parent?->getTranslation('category_name', app()->getLocale()) ?? '-' }}
            @endscope

            @scope('cell_attributes', $category)
                @if ($category->attributes->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($category->attributes->sortBy(fn ($attribute) => $attribute->getTranslation('name', app()->getLocale()))->take(3) as $attribute)
                            <x-mary-badge
                                :value="$attribute->getTranslation('name', app()->getLocale())"
                                class="{{ $attribute->is_active ? 'badge-info badge-outline' : 'badge-ghost' }}"
                            />
                        @endforeach

                        @if ($category->attributes_count > 3)
                            <x-mary-badge :value="'+' . ($category->attributes_count - 3)" class="badge-neutral badge-outline" />
                        @endif
                    </div>
                @else
                    <span class="text-sm text-base-content/60">{{ __('common_no_attributes') }}</span>
                @endif
            @endscope

            @scope('actions', $category)
                <x-backend.action-dropdown menu-class="!w-44">
                    <x-mary-menu-item
                        :title="__('common_edit')"
                        :link="route('admin.categories.edit', $category)"
                        icon="o-pencil-square"
                    />
                    <x-mary-menu-separator />
                    <x-mary-menu-item
                        :title="__('common_delete')"
                        icon="o-trash"
                        class="text-error"
                        wire:click.stop="confirmDeleteCategory({{ $category->id }})"
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
        close-on-escape
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
                :label="__('backend_categories_filters_structure')"
                wire:model.live="structureFilter"
                :options="$structureOptions"
                option-value="id"
                option-label="name"
                icon="o-squares-2x2"
                :placeholder="__('backend_categories_filters_all_structures')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('backend_categories_fields_attributes')"
                wire:model.live="attributePresenceFilter"
                :options="$attributePresenceOptions"
                option-value="id"
                option-label="name"
                icon="o-adjustments-horizontal"
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
