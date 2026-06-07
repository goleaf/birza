@props([
    'label' => null,
    'hint' => null,
    'disk' => 'public',
    'folder' => 'markdown',
    'config' => [],
])

@once
    @push('head-scripts')
        <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
        <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    @endpush
@endonce

<x-mary-markdown
    {{ $attributes }}
    :label="$label"
    :hint="$hint"
    :disk="$disk"
    :folder="$folder"
    :config="$config"
/>
