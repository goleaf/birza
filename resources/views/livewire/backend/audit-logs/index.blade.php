<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('audit_logs.navigation')],
        ]"
    />

    <x-mary-header :title="__('audit_logs.title')" :subtitle="__('audit_logs.description')" separator progress-indicator>
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
            :rows="$logs"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 25, 50, 100]"
            striped
            show-empty-text
            :empty-text="__('audit_logs.empty')"
        >
            @scope('cell_created_at', $log)
                {{ $log->created_at?->format('Y-m-d H:i:s') ?? __('common_not_specified') }}
            @endscope

            @scope('cell_action', $log)
                <code class="rounded bg-base-200 px-2 py-1 text-xs">{{ $log->action }}</code>
            @endscope

            @scope('cell_actor', $log)
                {{ $log->actorLabel() }}
            @endscope

            @scope('cell_actor_role', $log)
                <x-mary-badge :value="$log->actor_role" class="badge-outline" />
            @endscope

            @scope('cell_auditable', $log)
                {{ $log->auditableLabel() }}
            @endscope

            @scope('cell_reason', $log)
                <span class="line-clamp-2">{{ $log->reason ?: __('common_not_specified') }}</span>
            @endscope

            @scope('actions', $log)
                <x-mary-button
                    :label="__('audit_logs.view_detail')"
                    :link="route('admin.audit.show', $log)"
                    icon="o-eye"
                    class="btn-sm btn-ghost"
                />
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-drawer
        wire:model="drawer"
        :title="__('audit_logs.filters')"
        right
        separator
        with-close-button
        class="w-full max-w-md"
    >
        <div class="space-y-4">
            <x-mary-select
                :label="__('audit_logs.action')"
                wire:model.live="action"
                :options="$actionOptions"
                option-value="id"
                option-label="name"
                :placeholder="__('audit_logs.all_actions')"
                placeholder-value=""
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-mary-input
                    :label="__('audit_logs.actor_id')"
                    wire:model.live.debounce.300ms="actorId"
                    type="number"
                    min="1"
                />
                <x-mary-select
                    :label="__('audit_logs.actor_role')"
                    wire:model.live="role"
                    :options="$roleOptions"
                    option-value="id"
                    option-label="name"
                    :placeholder="__('audit_logs.all_roles')"
                    placeholder-value=""
                />
            </div>

            <x-mary-select
                :label="__('audit_logs.entity_type')"
                wire:model.live="entityType"
                :options="$entityOptions"
                option-value="id"
                option-label="name"
                :placeholder="__('audit_logs.all_entities')"
                placeholder-value=""
            />

            <x-mary-input
                :label="__('audit_logs.entity_id')"
                wire:model.live.debounce.300ms="entityId"
                type="number"
                min="1"
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-mary-input :label="__('audit_logs.date_from')" wire:model.live="dateFrom" type="date" />
                <x-mary-input :label="__('audit_logs.date_to')" wire:model.live="dateTo" type="date" />
            </div>
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
