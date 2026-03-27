    <div class="min-h-screen backdrop-blur-sm bg-white/30">
        <!-- Navigation -->
        <nav class="bg-gradient-to-r from-gray-800 to-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <div class="flex-shrink-0">
                        <a href="/" class="flex items-center">
                            <svg class="w-8 h-8 text-white mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 6l9-4 9 4v12l-9 4-9-4V6z" />
                            </svg>
                            <span class="text-2xl font-bold text-white">{{ config('app.name', __('welcome_app_name')) }}
                            </span>
                        </a>
                    </div>

                    <div class="flex items-center space-x-2">
                        @foreach (config('app.locales') as $lang)
                            <a href="{{ route('language.switch', $lang) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg {{ app()->getLocale() == $lang ? 'bg-gray-700 text-white' : 'text-white hover:bg-gray-700' }}">
                                {{ strtoupper($lang) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <x-ui.flash-messages />
        </div>

        <!-- Hero Section -->
        <div class="bg-gradient-to-b from-gray-50 to-transparent py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">{{ __('welcome_hero_title') }}</h1>
                <p class="text-xl text-gray-800 mb-8">{{ __('welcome_hero_subtitle') }}</p>
            </div>
        </div>

        <!-- Login Options -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Buyer Login -->
                <div class="bg-white/80 backdrop-blur-md p-8 rounded-xl shadow-xl transform transition duration-300">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-gray-100 rounded-lg mr-4">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('welcome_buyer_access_title') }}</h2>
                    </div>
                    <ul class="mb-8 text-gray-600">
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_buyer_feature_1') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_buyer_feature_2') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_buyer_feature_3') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_buyer_feature_4') }}
                        </li>
                    </ul>
                    <div class="space-y-4">
                        <a href="{{ route('buyer.login') }}"
                            class="block w-full bg-gradient-to-r from-gray-600 to-gray-800 text-white text-center px-6 py-3 rounded-lg font-medium hover:from-gray-700 hover:to-gray-900 transition duration-300">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                {{ __('welcome_buyer_login_button') }}
                            </div>
                        </a>
                        <a href="{{ route('buyer.register') }}"
                            class="block w-full bg-white text-gray-700 text-center px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition duration-300 border border-gray-600">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                {{ __('welcome_buyer_register_button') }}
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Seller Login -->
                <div class="bg-white/80 backdrop-blur-md p-8 rounded-xl shadow-xl transform transition duration-300">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-gray-100 rounded-lg mr-4">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('welcome_seller_access_title') }}</h2>
                    </div>
                    <ul class="mb-8 text-gray-600">
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_seller_feature_1') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_seller_feature_2') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_seller_feature_3') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('welcome_seller_feature_4') }}
                        </li>
                    </ul>
                    <div class="space-y-4">
                        <a href="{{ route('seller.login') }}"
                            class="block w-full bg-gradient-to-r from-gray-600 to-gray-800 text-white text-center px-6 py-3 rounded-lg font-medium hover:from-gray-700 hover:to-gray-900 transition duration-300">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                {{ __('welcome_seller_login_button') }}
                            </div>
                        </a>
                        <a href="{{ route('seller.register') }}"
                            class="block w-full bg-white text-gray-700 text-center px-6 py-3 rounded-lg font-medium hover:bg-gray-50 transition duration-300 border border-gray-600">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                {{ __('welcome_seller_register_button') }}
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Products Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('welcome_product_categories_title') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <img src="https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?ixlib=rb-4.0.3"
                        alt="{{ __('welcome_product_category_1_title') }}" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-lg font-semibold">{{ __('welcome_product_category_1_title') }}</h3>
                    <p class="text-gray-600">{{ __('welcome_product_category_1_description') }}</p>
                </div>
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <img src="https://images.unsplash.com/photo-1544025162-d76694265947?ixlib=rb-4.0.3"
                        alt="{{ __('welcome_product_category_2_title') }}" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-lg font-semibold">{{ __('welcome_product_category_2_title') }}</h3>
                    <p class="text-gray-600">{{ __('welcome_product_category_2_description') }}</p>
                </div>
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <img src="https://images.unsplash.com/photo-1598182198871-d3f4ab4fd181?ixlib=rb-4.0.3"
                        alt="{{ __('welcome_product_category_3_title') }}" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-lg font-semibold">{{ __('welcome_product_category_3_title') }}</h3>
                    <p class="text-gray-600">{{ __('welcome_product_category_3_description') }}</p>
                </div>
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <img src="https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?ixlib=rb-4.0.3"
                        alt="{{ __('welcome_product_category_4_title') }}" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-lg font-semibold">{{ __('welcome_product_category_4_title') }}</h3>
                    <p class="text-gray-600">{{ __('welcome_product_category_4_description') }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('welcome_community_title') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold">{{ __('welcome_seller_count') }}</h3>
                            <p class="text-3xl font-bold text-gray-600">
                                50
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold">{{ __('welcome_product_categories_count_title') }}</h3>
                            <p class="text-3xl font-bold text-gray-600">{{ __('welcome_product_categories_count') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold">{{ __('welcome_buyer_count') }}</h3>
                            <p class="text-3xl font-bold text-gray-600">{{ __('welcome_happy_customers_count') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

{{-- 
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('welcome_testimonials_title') }}</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <p class="text-gray-600 mb-4">{{ __('welcome_testimonial_1_text') }}</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-full"></div>
                        <div class="ml-3">
                            <p class="font-semibold">{{ __('welcome_testimonial_1_name') }}</p>
                            <p class="text-sm text-gray-500">{{ __('welcome_testimonial_1_role') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <p class="text-gray-600 mb-4">{{ __('welcome_testimonial_2_text') }}</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-full"></div>
                        <div class="ml-3">
                            <p class="font-semibold">{{ __('welcome_testimonial_2_name') }}</p>
                            <p class="text-sm text-gray-500">{{ __('welcome_testimonial_2_role') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                    <p class="text-gray-600 mb-4">{{ __('welcome_testimonial_3_text') }}</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-full"></div>
                        <div class="ml-3">
                            <p class="font-semibold">{{ __('welcome_testimonial_3_name') }}</p>
                            <p class="text-sm text-gray-500">{{ __('welcome_testimonial_3_role') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 --}}

        <!-- FAQ Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('main_faq_faq_title') }}</h2>
            <div class="space-y-4">
                @for ($i = 1; $i <= 10; $i++)
                    @if(__('main_faq_faq_' . $i . '_question') !== 'main_faq.faq_' . $i . '_question' && 
                        __('main_faq_faq_' . $i . '_answer') !== 'main_faq.faq_' . $i . '_answer')
                        <div x-data="{ open: false }" class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl">
                            <button type="button" x-on:click="open = !open" class="flex justify-between items-center w-full p-6">
                                <h3 class="text-lg font-semibold">{{ __('main_faq_faq_' . $i . '_question') }}</h3>
                                <svg x-bind:class="{ 'rotate-180': open }" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak x-transition class="px-6 pb-6">
                                <p class="text-gray-600">{{ __('main_faq_faq_' . $i . '_answer') }}</p>
                            </div>
                        </div>
                    @endif
                @endfor
            </div>
        </div>

        @include('layouts.frontend.footer')

    </div>

    @wireUiScripts
    @livewireScripts
</body>

</html>
