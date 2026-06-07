<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('messages.admin_title')],
        ]"
    />

    <x-mary-header :title="__('messages.admin_title')" :subtitle="__('messages.admin_subtitle')" separator progress-indicator>
        <x-slot:actions>
            <x-mary-select
                wire:model.live="filter"
                :options="$filterOptions"
                option-value="id"
                option-label="name"
                class="min-w-52"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <div class="divide-y divide-base-200">
            @forelse ($conversations as $conversation)
                <a
                    href="{{ route('admin.messages.show', $conversation) }}"
                    class="block py-4 transition hover:bg-base-200/60"
                >
                    <div class="flex flex-col gap-3 px-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate font-semibold">
                                    {{ $conversation->buyer?->company_name ?: $conversation->buyer?->name }}
                                    <span class="text-base-content/40">/</span>
                                    {{ $conversation->seller?->company_name ?: $conversation->seller?->name }}
                                </h2>
                                <x-mary-badge :value="$conversation->status->label()" class="badge-outline" />
                            </div>

                            <p class="mt-1 text-sm text-base-content/60">{{ $conversation->relatedLabel() }}</p>
                            <p class="mt-2 line-clamp-2 text-sm">
                                {{ $conversation->latestMessage?->preview() ?? __('messages.no_messages_yet') }}
                            </p>
                        </div>

                        <div class="text-sm text-base-content/60 sm:text-right">
                            {{ $conversation->last_message_at?->format('Y-m-d H:i') ?? $conversation->updated_at?->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="py-12 text-center text-sm text-base-content/60">
                    {{ __('messages.no_conversations') }}
                </div>
            @endforelse
        </div>
    </x-mary-card>

    {{ $conversations->links() }}
</div>
