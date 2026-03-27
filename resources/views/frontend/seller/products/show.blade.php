<!-- start extends -->
@extends('layouts.frontend.app')
<!-- end extends -->

<!-- start section -->
@section('content')
    <!-- start main container -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- start white container -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <!-- start content container -->
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- start grid container -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- start image column -->
                    <div>
                        @if($product->image)
                            <img 
                                src="{{ Storage::url($product->image) }}"
                                alt="{{ $product->name }}"
                                class="w-full rounded-lg shadow-lg"
                            >
                        @else
                            <!-- start placeholder container -->
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="h-16 w-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <!-- end placeholder container -->
                        @endif
                    </div>
                    <!-- end image column -->

                    <!-- start details column -->
                    <div>
                        <!-- start title -->
                        <h1 class="text-3xl font-bold mb-4">
                            {{ $product->name }}
                        </h1>
                        <!-- end title -->

                        <!-- start details container -->
                        <div class="space-y-4">
                            <!-- start price -->
                            <p class="text-2xl font-bold text-green-600">
                                €{{ number_format($product->price, 2) }} / {{ $product->unit }}
                            </p>
                            <!-- end price -->

                            <!-- start info grid -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- start left column -->
                                <div>
                                    <!-- start category -->
                                    <p class="text-gray-600">
                                        <strong>{{ __('product_category') }}:</strong> 
                                        {{ $product->category->name }}
                                    </p>
                                    <!-- end category -->

                                    <!-- start country -->
                                    <p class="text-gray-600">
                                        <strong>{{ __('product_country_of_origin') }}:</strong>
                                        {{ $product->country_of_origin }}
                                    </p>
                                    <!-- end country -->
                                </div>
                                <!-- end left column -->

                                <!-- start right column -->
                                <div>
                                    @if($product->is_organic)
                                        <!-- start organic badge -->
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            {{ __('product_is_organic') }}
                                        </span>
                                        <!-- end organic badge -->
                                    @endif
                                </div>
                                <!-- end right column -->
                            </div>
                            <!-- end info grid -->

                            <!-- start description section -->
                            <div class="mt-4">
                                <h3 class="font-bold mb-2">
                                    {{ __('product_description') }}
                                </h3>
                                <p class="text-gray-700">
                                    {{ $product->description ?? '-' }}
                                </p>
                            </div>
                            <!-- end description section -->

                            <!-- start stock section -->
                            <div class="mt-4">
                                <h3 class="font-bold mb-2">
                                    {{ __('product_stock') }}
                                </h3>
                                <p class="text-gray-700">
                                    {{ $product->stock }}
                                </p>
                            </div>
                            <!-- end stock section -->
                        </div>
                        <!-- end details container -->
                    </div>
                    <!-- end details column -->
                </div>
                <!-- end grid container -->
            </div>
            <!-- end content container -->
        </div>
        <!-- end white container -->
    </div>
    <!-- end main container -->
@endsection
<!-- end section -->
