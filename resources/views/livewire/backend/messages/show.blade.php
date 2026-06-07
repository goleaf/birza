<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('messages.admin_title'), 'link' => route('admin.messages.index')],
            ['label' => __('messages.conversation')],
        ]"
    />

    <x-mary-header
        :title="__('messages.admin_conversation_title', ['id' => $conversation->id])"
        :subtitle="$conversation->relatedLabel()"
        separator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('messages.back_to_inbox')"
                :link="route('admin.messages.index')"
                icon="o-arrow-left"
                class="btn-ghost"
            />
        </x-slot:actions>
    </x-mary-header>

    <x-mary-card shadow>
        <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="font-medium text-base-content/60">{{ __('messages.buyer') }}</dt>
                <dd>{{ $conversation->buyer?->company_name ?: $conversation->buyer?->name }}</dd>
            </div>
            <div>
                <dt class="font-medium text-base-content/60">{{ __('messages.seller') }}</dt>
                <dd>{{ $conversation->seller?->company_name ?: $conversation->seller?->name }}</dd>
            </div>
            <div>
                <dt class="font-medium text-base-content/60">{{ __('messages.status_label') }}</dt>
                <dd>{{ $conversation->status->label() }}</dd>
            </div>
            <div>
                <dt class="font-medium text-base-content/60">{{ __('messages.last_message') }}</dt>
                <dd>{{ $conversation->last_message_at?->format('Y-m-d H:i') ?? __('common_not_specified') }}</dd>
            </div>
        </dl>
    </x-mary-card>

    <x-mary-card shadow>
        <div class="space-y-4">
            @forelse ($messages as $message)
                <div class="rounded-lg border border-base-200 bg-base-100 p-4">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 text-xs text-base-content/60">
                        <span>{{ $message->senderLabel() }} · {{ $message->sender_role->label() }}</span>
                        <span>{{ $message->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    <p class="whitespace-pre-line text-sm leading-6">{{ $message->body }}</p>
                </div>
            @empty
                <div class="py-12 text-center text-sm text-base-content/60">
                    {{ __('messages.empty_conversation') }}
                </div>
            @endforelse
        </div>
    </x-mary-card>

    {{ $messages->links() }}
</div>
