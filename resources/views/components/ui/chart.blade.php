@once
    @push('head-scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    @endpush
@endonce

<x-mary-chart {{ $attributes }} />
