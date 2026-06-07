@props([
    'labelClass' => 'flex-1 !border-0 rounded-lg px-4 py-4 text-center text-sm font-medium text-gray-700 transition-colors hover:!bg-gray-200',
    'activeClass' => '!border-0 !bg-gradient-to-r !from-blue-500 !to-blue-600 !text-white !shadow-sm',
    'labelDivClass' => 'flex overflow-x-auto gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm',
    'tabsClass' => 'relative w-full',
])

<x-mary-tabs
    {{ $attributes }}
    :label-class="$labelClass"
    :active-class="$activeClass"
    :label-div-class="$labelDivClass"
    :tabs-class="$tabsClass"
>
    {{ $slot }}
</x-mary-tabs>
