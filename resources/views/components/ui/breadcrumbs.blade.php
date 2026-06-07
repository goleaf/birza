@props(['items' => []])

<div {{ $attributes->class('overflow-x-auto') }}>
    <x-mary-breadcrumbs
        :items="$items"
        :no-wire-navigate="true"
        link-item-class="inline-flex items-center gap-1.5 text-sm font-medium text-base-content/70 transition hover:text-primary hover:no-underline"
        text-item-class="inline-flex items-center gap-1.5 text-sm font-semibold text-base-content"
        separator-class="mx-2 h-3.5 w-3.5 text-base-content/30"
        class="min-w-max rounded-2xl border border-base-300/70 bg-base-100 px-4 py-3 shadow-sm"
    />
</div>
