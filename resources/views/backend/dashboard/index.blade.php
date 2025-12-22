@extends('layouts.backend.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">
                    {{ __('backend.dashboard.title') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-100 p-4 rounded-lg">
                    <h5 class="font-medium text-blue-800 mb-2">
                        {{ __('backend.dashboard.stats.total_categories') }}
                    </h5>
                    <p class="text-4xl font-bold text-blue-900 text-center">{{ $totalCategories }}</p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <h5 class="font-medium text-green-800 mb-2">
                        {{ __('backend.dashboard.stats.total_products') }}
                    </h5>
                    <p class="text-4xl font-bold text-green-900 text-center">{{ $totalProducts }}</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg">
                    <h5 class="font-medium text-yellow-800 mb-2">
                        {{ __('backend.dashboard.stats.total_orders') }}
                    </h5>
                    <p class="text-4xl font-bold text-yellow-900 text-center">{{ $totalOrders }}</p>
                </div>
            </div>

            <div class="mt-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-xl font-bold mb-4">
                            {{ __('backend.dashboard.recent_activity.title') }}
                        </h3>
                        <ul class="divide-y divide-gray-200">
                            @foreach($recentActivities as $activity)
                                <li class="py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-100">
                                                <span class="text-sm font-medium leading-none text-blue-600">
                                                    {{ substr($activity->type ?? 'A', 0, 1) }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $activity->description }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
