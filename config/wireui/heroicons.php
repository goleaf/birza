<?php

return [
    /*
        |--------------------------------------------------------------------------
        | Icons Variants
        |--------------------------------------------------------------------------
        |
        | The icon variant can be 'solid' or 'outline'
        | <x-icon solid />
        | <x-icon outline />
        | <x-icon variant="outline" />
        |
    */
    'variant' => 'outline',

    /*
        |--------------------------------------------------------------------------
        | Icon component alias
        |--------------------------------------------------------------------------
        |
        | The component alias to import in the blade/livewire component
        | Set to false to disable the component.
        | <x-icon ... />
        |
    */
    // WireUI reserves <x-icon /> for its own icon component, and exposes heroicons as <x-heroicons />.
    // Example: <x-heroicons name="..." />
    'alias' => 'heroicons',
];
