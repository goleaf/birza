<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('audit_logs.navigation'), 'link' => route('admin.audit.index')],
            ['label' => '#' . $auditLog->id],
        ]"
    />

    <x-mary-header
        :title="__('audit_logs.detail') . ' #' . $auditLog->id"
        :subtitle="$auditLog->action"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('admin.audit.index')"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <dl class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($details as $detail)
                <div class="rounded-lg border border-base-200 p-4">
                    <dt class="text-xs font-semibold uppercase text-base-content/50">
                        {{ $detail['label'] }}
                    </dt>
                    <dd class="mt-1 break-words text-sm font-medium text-base-content">
                        {{ $detail['value'] ?: __('common_not_specified') }}
                    </dd>
                </div>
            @empty
                <div class="text-sm text-base-content/60">{{ __('common_not_specified') }}</div>
            @endforelse
        </dl>
    </x-mary-card>

    <div class="grid gap-6 xl:grid-cols-3">
        @forelse ($payloads as $payload)
            <x-mary-card :title="$payload['title']" shadow>
                <pre class="max-h-[32rem] overflow-auto rounded-lg bg-base-200 p-4 text-xs leading-5 text-base-content">{{ $payload['content'] }}</pre>
            </x-mary-card>
        @empty
            <x-mary-alert
                :title="__('common_not_specified')"
                icon="o-information-circle"
                class="alert-info alert-soft"
            />
        @endforelse
    </div>
</div>
