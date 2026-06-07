@props([
    'menuClass' => '!w-52',
])

<div class="flex justify-end">
    <x-mary-dropdown right no-x-anchor>
        <x-slot:trigger class="list-none">
            <span class="btn btn-ghost btn-sm btn-circle">
                <x-mary-icon name="o-ellipsis-horizontal" class="h-5 w-5" />
                <span class="sr-only">{{ __('common_actions') }}</span>
            </span>
        </x-slot:trigger>

        <x-mary-menu class="{{ $menuClass }}">
            {{ $slot }}
        </x-mary-menu>
    </x-mary-dropdown>
</div>
