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
                        <img
                            src="{{ $product->imageUrl('large') }}"
                            alt="{{ $product->name }}"
                            class="aspect-[4/3] w-full rounded-lg object-cover shadow-lg"
                            loading="lazy"
                            width="1200"
                            height="900"
                        >
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
                                        <x-ui.badge
                                            :value="__('product_is_organic')"
                                            color="success"
                                            soft
                                            class="font-medium"
                                        />
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
