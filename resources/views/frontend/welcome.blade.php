    <div class="min-h-screen backdrop-blur-sm bg-white/30">
        <!-- Navigation -->
        <nav class="bg-gradient-to-r from-gray-800 to-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <div class="flex-shrink-0">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <x-ui.icon name="cube-transparent" class="mr-2 h-8 w-8 text-white" />
                            <span class="text-2xl font-bold text-white">{{ config('app.name', __('welcome_app_name')) }}
                            </span>
                        </a>
                    </div>

                    <div class="flex items-center space-x-2">
                        @foreach ($locales as $locale)
                            <a href="{{ route('language.switch', $locale['code']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg {{ $locale['isCurrent'] ? 'bg-gray-700 text-white' : 'text-white hover:bg-gray-700' }}">
                                {{ $locale['label'] }}
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
                            <x-ui.icon name="user-circle" class="h-8 w-8 text-gray-600" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('welcome_buyer_access_title') }}</h2>
                    </div>
                    <ul class="mb-8 text-gray-600">
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_buyer_feature_1') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_buyer_feature_2') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_buyer_feature_3') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_buyer_feature_4') }}
                        </li>
                    </ul>
                    <div class="space-y-4">
                        <x-ui.button
                            :href="route('buyer.login')"
                            primary
                            icon="arrow-right"
                            class="w-full justify-center"
                            :label="__('welcome_buyer_login_button')"
                        />
                        <x-ui.button
                            :href="route('buyer.register')"
                            secondary
                            outline
                            icon="user-plus"
                            class="w-full justify-center"
                            :label="__('welcome_buyer_register_button')"
                        />
                    </div>
                </div>

                <!-- Seller Login -->
                <div class="bg-white/80 backdrop-blur-md p-8 rounded-xl shadow-xl transform transition duration-300">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-gray-100 rounded-lg mr-4">
                            <x-ui.icon name="building-storefront" class="h-8 w-8 text-gray-600" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('welcome_seller_access_title') }}</h2>
                    </div>
                    <ul class="mb-8 text-gray-600">
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_seller_feature_1') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_seller_feature_2') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_seller_feature_3') }}
                        </li>
                        <li class="flex items-center mb-2">
                            <x-ui.icon name="check" class="mr-2 h-5 w-5 text-gray-500" />
                            {{ __('welcome_seller_feature_4') }}
                        </li>
                    </ul>
                    <div class="space-y-4">
                        <x-ui.button
                            :href="route('seller.login')"
                            primary
                            icon="arrow-right"
                            class="w-full justify-center"
                            :label="__('welcome_seller_login_button')"
                        />
                        <x-ui.button
                            :href="route('seller.register')"
                            secondary
                            outline
                            icon="user-plus"
                            class="w-full justify-center"
                            :label="__('welcome_seller_register_button')"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Products Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('welcome_product_categories_title') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach ($featuredCategories as $featuredCategory)
                    <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                        <img src="{{ $featuredCategory['imageUrl'] }}"
                            alt="{{ $featuredCategory['title'] }}" class="w-full h-48 object-cover rounded-lg mb-4">
                        <h3 class="text-lg font-semibold">{{ $featuredCategory['title'] }}</h3>
                        <p class="text-gray-600">{{ $featuredCategory['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('welcome_community_title') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                @foreach ($communityStats as $stat)
                    <div class="bg-white/80 backdrop-blur-md rounded-lg shadow-xl p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                @if ($stat['icon'] === 'categories')
                                    <x-ui.icon name="squares-2x2" class="h-6 w-6 text-gray-600" />
                                @else
                                    <x-ui.icon name="users" class="h-6 w-6 text-gray-600" />
                                @endif
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold">{{ $stat['title'] }}</h3>
                                <p class="text-3xl font-bold text-gray-600">{{ $stat['value'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
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
                        <x-mary-collapse class="bg-white/80 backdrop-blur-md shadow-xl">
                            <x-slot:heading class="text-lg font-semibold text-gray-900">
                                {{ __('main_faq_faq_' . $i . '_question') }}
                            </x-slot:heading>

                            <x-slot:content class="text-gray-600">
                                <p class="text-gray-600">{{ __('main_faq_faq_' . $i . '_answer') }}</p>
                            </x-slot:content>
                        </x-mary-collapse>
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
