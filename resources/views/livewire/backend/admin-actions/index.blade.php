<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('admin_actions_title')],
        ]"
    />

    <x-mary-header
        :title="__('admin_actions_title')"
        :subtitle="__('admin_actions_subtitle')"
        separator
        progress-indicator
    />

    <x-mary-card shadow>
        <x-mary-table
            :headers="$headers"
            :rows="$actions"
            with-pagination
            per-page="perPage"
            :per-page-values="[10, 20, 50, 100]"
            striped
            show-empty-text
        >
            @scope('cell_created_at', $action)
                <span class="whitespace-nowrap text-sm">
                    {{ $action->created_at?->format('Y-m-d H:i') }}
                </span>
            @endscope

            @scope('cell_actor', $action)
                <div class="space-y-1">
                    <div class="font-medium">
                        {{ $action->actor?->name ?? __('admin_actions_actor_unknown') }}
                    </div>
                    <div class="text-xs text-base-content/60">
                        {{ $action->actor?->email ?? __('admin_actions_actor_missing') }}
                    </div>
                </div>
            @endscope

            @scope('cell_action', $action)
                <x-mary-badge :value="$action->action" class="badge-outline" />
            @endscope

            @scope('cell_entity', $action)
                <span class="text-sm">{{ $action->entityLabel() }}</span>
            @endscope

            @scope('cell_reason', $action)
                <span class="text-sm">{{ $action->reason ?: __('common_not_specified') }}</span>
            @endscope

            @scope('actions', $action)
                <x-mary-dropdown>
                    <x-slot:trigger>
                        <x-mary-button icon="o-eye" class="btn-ghost btn-sm" />
                    </x-slot:trigger>

                    <div class="w-96 max-w-[calc(100vw-2rem)] space-y-3 p-4">
                        <div>
                            <div class="text-xs font-semibold uppercase text-base-content/60">
                                {{ __('admin_actions_details_old_values') }}
                            </div>
                            <pre class="mt-1 max-h-48 overflow-auto rounded bg-base-200 p-3 text-xs">{{ json_encode($action->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-' }}</pre>
                        </div>

                        <div>
                            <div class="text-xs font-semibold uppercase text-base-content/60">
                                {{ __('admin_actions_details_new_values') }}
                            </div>
                            <pre class="mt-1 max-h-48 overflow-auto rounded bg-base-200 p-3 text-xs">{{ json_encode($action->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-' }}</pre>
                        </div>

                        <div>
                            <div class="text-xs font-semibold uppercase text-base-content/60">
                                {{ __('admin_actions_details_request') }}
                            </div>
                            <div class="mt-1 text-xs text-base-content/70">
                                {{ $action->ip_address ?: '-' }}
                                <br>
                                {{ $action->user_agent ?: '-' }}
                            </div>
                        </div>
                    </div>
                </x-mary-dropdown>
            @endscope
        </x-mary-table>
    </x-mary-card>
</div>
