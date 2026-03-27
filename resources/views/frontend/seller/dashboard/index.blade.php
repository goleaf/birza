<div>
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        {{ __('dashboard_company_ads') }}
    </div>


    <!-- Categories -->
    @include('frontend.seller.dashboard.partials.categories')




    <!-- Orders Overview -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold">{{ __('dashboard_orders_overview') }}</h3>
            <a href="{{ route('seller.orders.index') }}"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium">{{ __('dashboard_view_all') }}</a>
        </div>

        {{--
        <!-- Top Stats Bar -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-white">

                <div class="text-center">
                    <p class="text-2xl font-bold">€124,592</p>
                    <p class="text-sm opacity-80">{{ __('dashboard_total_revenue') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold">1,234</p>
                    <p class="text-sm opacity-80">{{ __('dashboard_total_orders') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold">89</p>
                    <p class="text-sm opacity-80">{{ __('dashboard_products_listed') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold">4.8</p>
                    <p class="text-sm opacity-80">{{ __('dashboard_average_rating') }}</p>
                </div>
            </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('seller.products.index') }}"
                class="bg-white/10 hover:bg-white/20 text-white font-bold py-4 px-6 rounded text-center transition duration-200">
                {{ __('product_list') }}
            </a>
            <a href="#"
                class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-6 rounded text-center transition duration-200 disabled">
                {{ __('product_add') }}
            </a>
        </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('dashboard_total_orders') }}</p>
                        <p class="text-xl font-bold">{{ $ordersData['total'] }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('dashboard_pending_orders') }}</p>
                        <p class="text-xl font-bold">{{ $ordersData['pending'] }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-2">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('dashboard_paid_orders') }}</p>
                        <p class="text-xl font-bold">{{ $ordersData['paid'] }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('dashboard_failed_orders') }}</p>
                        <p class="text-xl font-bold">{{ $ordersData['failed'] }}</p>
                    </div>
                    <div class="bg-red-100 rounded-full p-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>
        --}}


        <div class="mt-6">

            @if (count($ordersData['recent']) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                
                            </th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('dashboard_order_id') }}
                            </th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('dashboard_date') }}
                            </th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('dashboard_status') }}
                            </th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('dashboard_total') }}
                            </th>
                            <th
                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('common_actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($ordersData['recent'] as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center">
                                    <img src="{{ Storage::url('products/' . $item['product']->product_image) }}" alt="{{ $item['product']->name }}" class="w-10 h-10 mr-3 rounded object-cover">
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $item['order']->id }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item['order']->created_at->format('Y-m-d H:i') }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $item['order']->payment_status === \App\Models\Order::STATUS['PENDING'] ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $item['order']->payment_status === \App\Models\Order::STATUS['PAID'] ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $item['order']->payment_status === \App\Models\Order::STATUS['FAILED'] ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ __('orders_status_3_' . strtolower($item['order']->payment_status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    €{{ number_format($item['total_price'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('seller.orders.show', $item['order']) }}"
                                        class="text-blue-600 hover:text-blue-900">
                                        {{ __('orders_view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                {{ __('dashboard_no_orders_yet') }}
            </div>
        @endif


        
        </div>
    </div>




    <!-- Promotional Banners -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-sm p-6 text-white">
            <h3 class="text-xl font-bold mb-2">{{ __('dashboard_premium_seller_program') }}</h3>
            <p class="mb-4 opacity-90">{{ __('dashboard_premium_seller_benefits') }}</p>
            <button
                class="bg-white text-purple-600 px-4 py-2 rounded font-semibold">{{ __('dashboard_learn_more') }}</button>
        </div>
        <div class="bg-gradient-to-r from-pink-600 to-red-600 rounded-lg shadow-sm p-6 text-white">
            <h3 class="text-xl font-bold mb-2">{{ __('dashboard_holiday_season_sale') }}</h3>
            <p class="mb-4 opacity-90">{{ __('dashboard_prepare_inventory') }}</p>
            <button class="bg-white text-pink-600 px-4 py-2 rounded font-semibold">{{ __('dashboard_get_ready') }}</button>
        </div>
    </div>





    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Profile Card -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold">{{ Auth::guard('seller')->user()->name }}</h3>
                    <p class="text-gray-500 text-sm">{{ Auth::guard('seller')->user()->email }}</p>
                </div>
            </div>
            <div class="border-t pt-4">
                <p class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">{{ __('dashboard_company') }}:</span>
                    <span
                        class="font-medium">{{ Auth::guard('seller')->user()->company_name ?: __('dashboard_not_set') }}</span>
                </p>
                <p class="flex justify-between text-sm">
                    <span class="text-gray-600">{{ __('dashboard_member_since') }}:</span>
                    <span class="font-medium">{{ Auth::guard('seller')->user()->created_at->format('d F, Y') }}</span>
                </p>
            </div>
        </div>

        <!-- Calendar -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('dashboard_calendar') }}</h3>
            <div class="grid grid-cols-7 gap-1">
                <div class="text-center text-sm font-medium text-gray-500">{{ __('dashboard_sun') }}</div>
                <div class="text-center text-sm font-medium text-gray-500">{{ __('dashboard_mon') }}</div>
                <div class="text-center text-sm font-medium text-gray-500">{{ __('dashboard_tue') }}</div>
                <div class="text-center text-sm font-medium text-gray-500">{{ __('dashboard_wed') }}</div>
                <div class="text-center text-sm font-medium text-gray-500">{{ __('dashboard_thu') }}</div>
                <div class="text-center text-sm font-medium text-gray-500">{{ __('dashboard_fri') }}</div>
                <div class="text-center text-sm font-medium text-gray-500">{{ __('dashboard_sat') }}</div>
                @php
                    $firstDayOfMonth = \Carbon\Carbon::now()->startOfMonth();
                    $lastDayOfMonth = \Carbon\Carbon::now()->endOfMonth();
                    $firstDayOfWeek = $firstDayOfMonth->dayOfWeek;
                    $daysInMonth = $lastDayOfMonth->day;
                    $currentDay = \Carbon\Carbon::now()->day;
                @endphp

                @for ($i = 0; $i < $firstDayOfWeek; $i++)
                    <div class="text-center py-1"></div>
                @endfor

                @for ($day = 1; $day <= $daysInMonth; $day++)
                    <div
                        class="text-center py-1 {{ $day == $currentDay ? 'bg-blue-100 rounded-full font-bold text-blue-600' : '' }}">
                        {{ $day }}
                    </div>
                @endfor
            </div>
        </div>

        <!-- Market Analysis -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('dashboard_market_analysis') }}</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('dashboard_market_trend') }}</span>
                    <span class="text-green-500 font-medium">{{ __('dashboard_bullish') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('dashboard_volatility') }}</span>
                    <span class="text-yellow-500 font-medium">{{ __('dashboard_medium') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('dashboard_competition') }}</span>
                    <span class="text-red-500 font-medium">{{ __('dashboard_high') }}</span>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <h4 class="text-sm font-medium mb-2">{{ __('dashboard_recommended_actions') }}</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• {{ __('dashboard_optimize_pricing') }}</li>
                        <li>• {{ __('dashboard_expand_product_range') }}</li>
                        <li>• {{ __('dashboard_increase_marketing') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">{{ __('dashboard_monthly_sales') }}</h3>
        <div class="h-48 flex items-end space-x-2">
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 60%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 70%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 40%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 80%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 95%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 60%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 45%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 75%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 85%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 90%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 85%"></div>
            <div class="w-1/12 bg-gradient-to-t from-blue-500 to-blue-300 rounded-t" style="height: 95%"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-gray-500">
            <span>{{ __('dashboard_jan') }}</span>
            <span>{{ __('dashboard_feb') }}</span>
            <span>{{ __('dashboard_mar') }}</span>
            <span>{{ __('dashboard_apr') }}</span>
            <span>{{ __('dashboard_may') }}</span>
            <span>{{ __('dashboard_jun') }}</span>
            <span>{{ __('dashboard_jul') }}</span>
            <span>{{ __('dashboard_aug') }}</span>
            <span>{{ __('dashboard_sep') }}</span>
            <span>{{ __('dashboard_oct') }}</span>
            <span>{{ __('dashboard_nov') }}</span>
            <span>{{ __('dashboard_dec') }}</span>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th
                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('dashboard_order_id') }}</th>
                        <th
                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('dashboard_product') }}</th>
                        <th
                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('dashboard_customer') }}</th>
                        <th
                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('dashboard_amount') }}</th>
                        <th
                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('dashboard_status') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#12345</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ __('dashboard_premium_widget') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ __('dashboard_john_doe') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">€299.99</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ __('dashboard_completed') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#12344</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ __('dashboard_basic_widget') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ __('dashboard_jane_smith') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">€149.99</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ __('dashboard_pending') }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Trading Performance -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('dashboard_trading_performance') }}</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span>{{ __('dashboard_win_rate') }}</span>
                    <span class="text-green-500 font-bold">68%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>{{ __('dashboard_profit_factor') }}</span>
                    <span class="text-blue-500 font-bold">2.4</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>{{ __('dashboard_average_trade') }}</span>
                    <span class="text-green-500 font-bold">+€234.56</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>{{ __('dashboard_drawdown') }}</span>
                    <span class="text-red-500 font-bold">-12.3%</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('dashboard_risk_management') }}</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span>{{ __('dashboard_position_size') }}</span>
                    <div class="w-1/2 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 65%"></div>
                    </div>
                    <span class="text-sm font-medium">65%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('dashboard_risk_level') }}</span>
                    <div class="w-1/2 bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-500 h-2 rounded-full" style="width: 45%"></div>
                    </div>
                    <span class="text-sm font-medium">45%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('dashboard_leverage') }}</span>
                    <div class="w-1/2 bg-gray-200 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: 30%"></div>
                    </div>
                    <span class="text-sm font-medium">30%</span>
                </div>
            </div>
        </div>
    </div>



    <!-- Market Sentiment -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">{{ __('dashboard_market_sentiment') }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">{{ __('dashboard_bulls_vs_bears') }}</h4>
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width: 65%"></div>
                    </div>
                    <span class="ml-2 text-sm">65%</span>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">{{ __('dashboard_volume') }}</h4>
                <p class="text-2xl font-bold text-gray-800">1.2M</p>
                <span class="text-green-500 text-sm">+12.3%</span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">{{ __('dashboard_volatility') }}</h4>
                <p class="text-2xl font-bold text-gray-800">{{ __('dashboard_medium') }}</p>
                <span class="text-yellow-500 text-sm">+5.2%</span>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-600 mb-2">{{ __('dashboard_trend') }}</h4>
                <p class="text-2xl font-bold text-gray-800">{{ __('dashboard_bullish') }}</p>
                <span class="text-green-500 text-sm">{{ __('dashboard_strong') }}</span>
            </div>
        </div>
    </div>

    <!-- Detailed Stats -->
    <div class="space-y-4">
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
                <span class="text-sm">{{ __('dashboard_low_risk') }} (68%)</span>
            </div>
            <div class="flex items-center space-x-2 mt-1">
                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <span class="text-sm">{{ __('dashboard_medium_risk') }} (24%)</span>
            </div>
            <div class="flex items-center space-x-2 mt-1">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <span class="text-sm">{{ __('dashboard_high_risk') }} (8%)</span>
            </div>
        </div>

    </div>
</div>
