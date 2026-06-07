@props([
    'title' => '',
    'description' => null,
    'confirmLabel' => __('common_delete'),
])

<x-mary-modal
    {{ $attributes }}
    :title="$title"
    box-class="max-w-md"
    separator
>
    <div class="flex items-start gap-4">
        <div class="rounded-full bg-error/10 p-3 text-error">
            <x-mary-icon name="o-exclamation-triangle" class="h-6 w-6" />
        </div>

        <div class="space-y-2">
            @if (filled($description))
                <p class="text-sm leading-6 text-base-content/70">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>

    <x-slot:actions>
        <x-mary-button
            :label="__('common_cancel')"
            wire:click="closeConfirmModal"
        />
        <x-mary-button
            :label="$confirmLabel"
            class="btn-error"
            wire:click="runConfirmedAction"
            spinner="runConfirmedAction"
        />
    </x-slot:actions>
</x-mary-modal>
