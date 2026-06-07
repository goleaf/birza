<div>
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 overflow-hidden shadow-2xl sm:rounded-lg">
        <div class="p-8 bg-opacity-90 border-b border-gray-200">
            <div class="grid grid-cols-3 gap-4 text-white">
                <div class="bg-white bg-opacity-10 p-4 rounded-lg">
1 - nepanauduotas kredito likutis
                    <p class="text-white text-2xl font-bold">
                        1 000 €
                    </p>
                    <span class="text-green-300 text-sm">
                        {{ __('dashboard_credit_balance') }}
                    </span>
                </div>

            
                <div class="bg-white bg-opacity-10 p-4 rounded-lg">
2 - panaudota kredito suma
                    <p class="text-white text-2xl font-bold">
                        2 000 €
                    </p>
                    <span class="text-green-300 text-sm">
                        {{ __('dashboard_credit_spent_amount') }}
                    </span>
                </div>

                <div class="bg-white bg-opacity-10 p-4 rounded-lg">
3 - suteikta kredito suma
                    <p class="text-white text-2xl font-bold">
                         3 000 €
                    </p>
                    <span class="text-blue-200 text-sm">
                        {{ __('dashboard_total_credit_amount') }}
                    </span>


                </div>
{{-- 
                <div class="bg-white bg-opacity-10 p-4 rounded-lg">
4 - balansas (likutis)
                    <p class="text-white text-2xl font-bold">
                        7 000 €<br>
                        <span class="text-xs">+ 10 000 € (2024-12-12)</span>
                    </p>
                    <span class="text-blue-200 text-sm">
                        {{ __('dashboard_total_credit_amount') }}
                    </span>

                </div>
 --}}


            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-blue-600 to-purple-600 overflow-hidden shadow-2xl sm:rounded-lg my-6">
        <a href="{{ route('buyer.products.index') }}"
            class="group relative w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl shadow-lg overflow-hidden transition duration-300 transform hover:-translate-y-1 hover:shadow-xl py-4 px-1 flex items-center justify-center">

            <div class="relative flex items-center justify-between w-full max-w-4xl">
                <div class="flex items-center space-x-6">
                    <span class="text-4xl font-bold">{{ __('product_search_list') }}</span>
                </div>

                <div class="flex items-center">
                    <x-ui.icon
                        name="arrow-right"
                        class="h-12 w-12 transform transition-transform duration-300 group-hover:translate-x-4"
                    />
                </div>
            </div>
        </a>
    </div>


    @include('frontend.buyer.dashboard.orders')


    <section class="my-6 rounded-lg bg-white p-4 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-semibold text-gray-900">{{ __('stock_alerts.dashboard_title') }}</h2>
            <a href="{{ route('buyer.stock-alerts.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                {{ __('stock_alerts.view_alerts') }}
            </a>
        </div>

        <div class="space-y-3">
            @forelse ($recentStockAlerts as $alert)
                @php
                    $product = $alert->product;
                    $isPurchasable = $product?->isPurchasableByBuyers() ?? false;
                    $imageUrl = $product ? data_get($product->imageLibraryPreview()->first(), 'url') : null;
                @endphp

                <a
                    href="{{ $product ? route('buyer.products.show', $product) : route('buyer.stock-alerts.index') }}"
                    class="block rounded-md border border-gray-100 p-3 transition hover:border-blue-200 hover:bg-blue-50"
                >
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-md bg-gray-100">
                            @if ($product)
                                <img
                                    src="{{ $imageUrl ?: asset('images/admin-product-placeholder.svg') }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center text-gray-400">
                                    <x-ui.icon name="photo" class="h-5 w-5" />
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900">
                                {{ $product?->name ?? __('stock_alerts.product_missing') }}
                            </p>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $isPurchasable ? __('stock_alerts.product_available') : __($alert->status->labelKey()) }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-md border border-dashed border-gray-200 p-4 text-sm text-gray-500">
                    {{ __('stock_alerts.empty') }}
                </div>
            @endforelse
        </div>
    </section>


    <div class="flex flex-wrap -mx-4 my-6">
        <!-- Left side - 2 blocks -->
        <div class="w-full lg:w-3/4 px-4">
            <!-- First block -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                
                {{--  
                <h3 class="text-xl font-semibold mb-4">{{ __('dashboard_stats') }}</h3>
                
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <h4 class="text-gray-500">{{ __('dashboard_total_transactions') }}</h4>
                            <span
                                class="text-green-500">{{ __('dashboard_percent_change', ['percent' => '12.5']) }}</span>
                        </div>
                        <p class="text-2xl font-bold">{{ __('dashboard_amount', ['amount' => '24,589.32']) }}</p>
                        <div class="text-sm text-gray-400">{{ __('dashboard_vs_previous_month') }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <h4 class="text-gray-500">{{ __('dashboard_active_orders') }}</h4>
                            <span class="text-red-500">{{ __('dashboard_percent_change', ['percent' => '-2.3']) }}</span>
                        </div>
                        <p class="text-2xl font-bold">147</p>
                        <div class="text-sm text-gray-400">{{ __('dashboard_across_categories', ['count' => '12']) }}
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="flex items-center justify-between">
                            <h4 class="text-gray-500">{{ __('dashboard_success_rate') }}</h4>
                            <span class="text-green-500">{{ __('dashboard_percent_change', ['percent' => '5.2']) }}</span>
                        </div>
                        <p class="text-2xl font-bold">98.3%</p>
                        <div class="text-sm text-gray-400">{{ __('dashboard_last_30_days') }}</div>
                    </div>
                </div>

                <!-- Transaction Graph -->
                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <h4 class="text-lg font-semibold mb-4">{{ __('dashboard_transaction_history') }}</h4>
                    <div class="h-64 bg-gray-100 rounded-lg flex items-end justify-between p-4">
                        <div class="w-8 bg-blue-500 rounded-t" style="height: 40%"></div>
                        <div class="w-8 bg-green-500 rounded-t" style="height: 65%"></div>
                        <div class="w-8 bg-purple-500 rounded-t" style="height: 45%"></div>
                        <div class="w-8 bg-red-500 rounded-t" style="height: 80%"></div>
                        <div class="w-8 bg-yellow-500 rounded-t" style="height: 75%"></div>
                        <div class="w-8 bg-indigo-500 rounded-t" style="height: 55%"></div>
                        <div class="w-8 bg-pink-500 rounded-t" style="height: 70%"></div>
                    </div>

                    <div class="mt-4 grid grid-cols-4 gap-2">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 mr-2 rounded-full"></div>
                            <span class="text-xs">{{ __('dashboard_deposits') }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 mr-2 rounded-full"></div>
                            <span class="text-xs">{{ __('dashboard_withdrawals') }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-purple-500 mr-2 rounded-full"></div>
                            <span class="text-xs">{{ __('dashboard_transfers') }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 mr-2 rounded-full"></div>
                            <span class="text-xs">{{ __('dashboard_investments') }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 mr-2 rounded-full"></div>
                            <span class="text-xs">{{ __('dashboard_fees') }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-indigo-500 mr-2 rounded-full"></div>
                            <span class="text-xs">{{ __('dashboard_refunds') }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-pink-500 mr-2 rounded-full"></div>
                            <span class="text-xs">{{ __('dashboard_other') }}</span>
                        </div>
                    </div>
                </div>

                --}}

                <!-- Sales Performance Graph -->
                <div class="bg-white p-4 rounded-lg shadow mb-6">
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold">{{ __('dashboard_sales_performance') }}</h4>
                        <p class="text-sm text-gray-500">{{ __('dashboard_sales_performance_subtitle') }}</p>
                    </div>

                    <x-ui.chart wire:model="salesPerformanceChart" class="h-80 rounded-lg bg-gray-50 p-4" />
                </div>

                <!-- Detailed Stats -->
                <div class="space-y-4">
   {{--  

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold mb-2">{{ __('dashboard_portfolio_distribution') }}</h4>
                        <div class="flex items-center">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: 45%"></div>
                            </div>
                            <span class="ml-2">45%</span>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">{{ __('dashboard_portfolio_description') }}</div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold mb-2">{{ __('dashboard_risk_assessment') }}</h4>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm">{{ __('dashboard_low_risk', ['percent' => '68']) }}</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-1">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-sm">{{ __('dashboard_medium_risk', ['percent' => '24']) }}</span>
                        </div>
                        <div class="flex items-center space-x-2 mt-1">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-sm">{{ __('dashboard_high_risk', ['percent' => '8']) }}</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold mb-2">{{ __('dashboard_market_analysis') }}</h4>
                        <p class="text-sm text-gray-600">
                            {{ __('dashboard_market_analysis_description') }}
                        </p>
                        <div class="flex justify-between mt-3 text-sm">
                            <span class="text-green-500">{{ __('dashboard_market_up', ['percent' => '3.2']) }}</span>
                            <span class="text-gray-500">{{ __('dashboard_updated_ago', ['time' => '5']) }}</span>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold mb-2">{{ __('dashboard_investment_opportunities') }}</h4>
                        <p class="text-sm text-gray-600 mb-3">
                            {{ __('dashboard_investment_description') }}
                        </p>
                        <div class="flex space-x-2">
                            <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                                {{ __('dashboard_explore_options') }}
                            </button>
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-md text-sm">
                                Learn More
                            </button>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold mb-2">{{ __('dashboard_recent_notifications') }}</h4>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <p class="text-sm">{{ __('dashboard_notification_1') }}</p>
                            </div>
                            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <p class="text-sm">{{ __('dashboard_notification_2') }}</p>
                            </div>
                            <div class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <p class="text-sm">{{ __('dashboard_notification_3') }}</p>
                            </div>
                        </div>
                        <a href="#"
                            class="text-blue-500 hover:text-blue-600 text-sm block mt-3">{{ __('dashboard_view_all_notifications') }}</a>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold mb-2">{{ __('dashboard_quick_actions') }}</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 p-3 rounded-lg text-center hover:bg-gray-100 cursor-pointer">
                                <i class="fas fa-chart-line text-blue-500 mb-2"></i>
                                <p class="text-sm">{{ __('dashboard_view_analytics') }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center hover:bg-gray-100 cursor-pointer">
                                <i class="fas fa-wallet text-green-500 mb-2"></i>
                                <p class="text-sm">{{ __('dashboard_add_funds') }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center hover:bg-gray-100 cursor-pointer">
                                <i class="fas fa-exchange-alt text-purple-500 mb-2"></i>
                                <p class="text-sm">{{ __('dashboard_transfer') }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center hover:bg-gray-100 cursor-pointer">
                                <i class="fas fa-cog text-gray-500 mb-2"></i>
                                <p class="text-sm">{{ __('dashboard_settings') }}</p>
                            </div>
                        </div>
                    </div>
--}}

                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold mb-2">{{ __('dashboard_calendar') }}</h4>
                        <div class="grid grid-cols-7 gap-1">
                            <div class="text-center text-sm font-medium text-gray-500">{{ __('calendar_mon') }}
                            </div>
                            <div class="text-center text-sm font-medium text-gray-500">{{ __('calendar_tue') }}
                            </div>
                            <div class="text-center text-sm font-medium text-gray-500">{{ __('calendar_wed') }}
                            </div>
                            <div class="text-center text-sm font-medium text-gray-500">{{ __('calendar_thu') }}
                            </div>
                            <div class="text-center text-sm font-medium text-gray-500">{{ __('calendar_fri') }}
                            </div>
                            <div class="text-center text-sm font-medium text-gray-500">{{ __('calendar_sat') }}
                            </div>
                            <div class="text-center text-sm font-medium text-gray-500">{{ __('calendar_sun') }}
                            </div>

                            @php
                                $firstDayOfMonth = \Carbon\Carbon::now()->startOfMonth();
                                $lastDayOfMonth = \Carbon\Carbon::now()->endOfMonth();
                                $firstDayOfWeek = ($firstDayOfMonth->dayOfWeek - 1 + 7) % 7; // Adjust for Monday as first day
                                $daysInMonth = $lastDayOfMonth->day;
                                $currentDay = \Carbon\Carbon::now()->day;
                            @endphp

                            @for ($i = 0; $i < $firstDayOfWeek; $i++)
                                <div class="p-2 text-center text-sm text-gray-300"></div>
                            @endfor

                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                <div
                                    class="p-2 text-center text-sm {{ $day == $currentDay ? 'bg-blue-100 rounded-full font-bold text-blue-600' : 'text-gray-700' }}">
                                    {{ $day }}
                                </div>
                            @endfor
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Right side - vertical long block -->
        <div class="w-full lg:w-1/4 px-4">
            <x-ui.card class="sticky top-6 shadow-lg" :title="__('dashboard_banners')">
                <x-mary-carousel
                    :slides="$bannerSlides"
                    autoplay
                    interval="5000"
                    without-arrows
                    class="!h-52 sm:!h-64"
                />
            </x-ui.card>
        </div>
    </div>


</div>
