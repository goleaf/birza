<?php

return [
    /*
        |--------------------------------------------------------------------------
        | Icons Variants
        |--------------------------------------------------------------------------
        |
        | The icon variant can be thin, light, fill, regular, duotone, bold
        | See the PhosphorIcons variants for more information.
        | <x-icon bold />
        | <x-icon duotone />
        | <x-icon variant="thin" />
        |
    */
    'variant' => 'regular',

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
    // Use a distinct alias so it doesn't collide with WireUI's own <x-icon /> component.
    // Example: <x-phosphor name="..." />
    'alias' => 'phosphor',
];
