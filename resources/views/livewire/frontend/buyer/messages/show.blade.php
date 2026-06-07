<div class="mx-auto max-w-5xl space-y-6">
    <x-buyer.breadcrumbs
        :items="[
            ['label' => __('messages.inbox'), 'link' => route('buyer.messages.index')],
            ['label' => __('messages.conversation')],
        ]"
    />

    <x-ui.header
        :title="$conversation->seller?->company_name ?: $conversation->seller?->name"
        :subtitle="$conversation->relatedLabel()"
    >
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.badge
                    :value="$conversation->status->label()"
                    color="primary"
                    soft
                />
                <x-ui.button
                    :href="route('buyer.messages.index')"
                    secondary
                    icon="arrow-left"
                    :label="__('messages.back_to_inbox')"
                />
                <x-ui.button
                    type="button"
                    secondary
                    outline
                    icon="archive-box"
                    wire:click="archive"
                    spinner="archive"
                    :label="__('messages.archive')"
                />
            </div>
        </x-slot:actions>
    </x-ui.header>

    @if (session('message'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.card class="rounded-lg shadow-sm" body-class="space-y-4">
        @forelse ($messages as $message)
            @php($isMine = $message->isFrom($actor))
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-2xl rounded-lg px-4 py-3 {{ $isMine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                    <div class="mb-1 text-xs {{ $isMine ? 'text-blue-100' : 'text-gray-500' }}">
                        {{ $message->senderLabel() }} · {{ $message->created_at?->format('Y-m-d H:i') }}
                    </div>
                    <p class="whitespace-pre-line text-sm leading-6">{{ $message->body }}</p>
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-sm text-gray-500">
                {{ __('messages.empty_conversation') }}
            </div>
        @endforelse

        {{ $messages->links() }}
    </x-ui.card>

    @if ($conversation->canReceiveMessages())
        <form wire:submit.prevent="sendMessage" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <label for="message-body" class="mb-2 block text-sm font-medium text-gray-700">
                {{ __('messages.write_message') }}
            </label>
            <textarea
                id="message-body"
                wire:model.defer="body"
                rows="4"
                maxlength="2000"
                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                placeholder="{{ __('messages.write_message_placeholder') }}"
            ></textarea>
            @error('body')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-3 flex justify-end">
                <x-ui.button
                    type="submit"
                    primary
                    icon="paper-airplane"
                    spinner="sendMessage"
                    :label="__('messages.send')"
                />
            </div>
        </form>
    @else
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            {{ __('messages.errors.conversation_closed') }}
        </div>
    @endif
</div>
