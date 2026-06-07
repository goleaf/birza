@props([
    'logs',
])

<x-mary-card :title="__('audit_logs.history')" shadow>
    @forelse ($logs as $log)
        <x-mary-list-item
            :item="$log"
            :link="route('admin.audit.show', $log)"
            :no-separator="$loop->last"
        >
            <x-slot:avatar>
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <x-mary-icon name="o-clipboard-document-check" class="h-5 w-5" />
                </div>
            </x-slot:avatar>

            <x-slot:value>
                <code class="rounded bg-base-200 px-2 py-1 text-xs">{{ $log->action }}</code>
            </x-slot:value>

            <x-slot:sub-value>
                {{ $log->actorLabel() }}
                &middot;
                {{ $log->created_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}
                @if ($log->reason)
                    &middot; {{ $log->reason }}
                @endif
            </x-slot:sub-value>
        </x-mary-list-item>
    @empty
        <x-mary-alert
            :title="__('audit_logs.no_history')"
            icon="o-information-circle"
            class="alert-info alert-soft"
        />
    @endforelse
</x-mary-card>
