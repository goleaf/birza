<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('admin.reports.title')],
        ]"
    />

    <x-mary-header
        :title="__('admin.reports.title')"
        :subtitle="__('admin.reports.subtitle')"
        separator
        progress-indicator
    >
        <x-slot:middle class="!justify-end">
            <x-mary-input
                :placeholder="__('admin.reports.search_placeholder')"
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
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$reports"
            :sort-by="$sortBy"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 15, 25, 50]"
            striped
            show-empty-text
            :empty-text="__('admin.reports.empty')"
        >
            @scope('cell_created_at', $report)
                <span class="whitespace-nowrap text-sm">
                    {{ $report->created_at?->format('Y-m-d H:i') }}
                </span>
            @endscope

            @scope('cell_product', $report)
                <div class="space-y-1">
                    <a
                        href="{{ route('admin.reports.show', $report) }}"
                        class="font-medium text-primary hover:underline"
                    >
                        {{ $report->product?->name ?? __('common_not_specified') }}
                    </a>
                    <div class="text-xs text-base-content/60">
                        #{{ $report->product_id }}
                    </div>
                </div>
            @endscope

            @scope('cell_seller', $report)
                {{ $report->product?->seller?->company_name ?: $report->product?->seller?->name ?: __('common_not_specified') }}
            @endscope

            @scope('cell_reason', $report)
                <x-mary-badge :value="$report->reasonLabel()" class="badge-outline" />
            @endscope

            @scope('cell_status', $report)
                <x-mary-badge :value="$report->statusLabel()" class="{{ $report->statusBadgeClass() }}" />
            @endscope

            @scope('cell_reporter', $report)
                <span class="text-sm">{{ $report->reporterLabel() }}</span>
            @endscope

            @scope('actions', $report)
                <x-mary-button
                    :label="__('common_view')"
                    :link="route('admin.reports.show', $report)"
                    icon="o-eye"
                    class="btn-ghost btn-sm"
                />
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
                :placeholder="__('admin.reports.search_placeholder')"
                wire:model.live.debounce.300ms="search"
                icon="o-magnifying-glass"
                clearable
            />

            <x-mary-select
                :label="__('admin.reports.filters.status')"
                wire:model.live="statusFilter"
                :options="$statusOptions"
                option-value="id"
                option-label="name"
                icon="o-check-badge"
                :placeholder="__('common_all')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('admin.reports.filters.reason')"
                wire:model.live="reasonFilter"
                :options="$reasonOptions"
                option-value="id"
                option-label="name"
                icon="o-flag"
                :placeholder="__('common_all')"
                placeholder-value=""
            />

            <x-mary-select
                :label="__('admin.reports.filters.seller')"
                wire:model.live="sellerFilter"
                :options="$sellerOptions"
                option-value="id"
                option-label="name"
                icon="o-building-storefront"
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
</div>
