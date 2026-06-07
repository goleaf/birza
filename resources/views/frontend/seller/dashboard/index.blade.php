@use('App\Enums\OrderStatus')

<div>
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        {{ __('dashboard_company_ads') }}
    </div>

    <div class="mb-6">
        <x-notifications.recent-panel
            :notifications="$recentNotifications"
            :href="route('seller.notifications.index')"
        />
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
                        <p class="text-xl font-bold">{{ $ordersData['counts'][OrderStatus::Pending->value] }}</p>
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
                        <p class="text-sm text-gray-500">{{ OrderStatus::Accepted->label() }}</p>
                        <p class="text-xl font-bold">{{ $ordersData['counts'][OrderStatus::Accepted->value] }}</p>
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
                        <p class="text-sm text-gray-500">{{ OrderStatus::Cancelled->label() }}</p>
                        <p class="text-xl font-bold">{{ $ordersData['counts'][OrderStatus::Cancelled->value] }}</p>
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
                                    <img src="{{ $item['product']?->imageUrl('thumb') ?? asset((string) config('images.fallbacks.product')) }}" alt="{{ $item['product']?->name ?? __('common_unnamed_product') }}" class="w-10 h-10 mr-3 rounded object-cover" loading="lazy">
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    #{{ $item['order']->id }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item['order']->created_at->format('Y-m-d H:i') }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-ui.badge
                                        :value="$item['order']->paymentStatusLabel()"
                                        :color="$item['order']->paymentStatusUiColor()"
                                        soft
                                        class="font-medium"
                                    />
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
            <x-ui.popover position="bottom-start" class="mb-4 block">
                <x-slot:trigger>
                    <button type="button" class="flex w-full items-center text-left">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                            <x-ui.icon name="user-circle" class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold">{{ Auth::guard('seller')->user()->name }}</h3>
                            <p class="text-sm text-gray-500">{{ Auth::guard('seller')->user()->email }}</p>
                        </div>
                    </button>
                </x-slot:trigger>

                <x-slot:content>
                    <div class="space-y-3">
                        <div class="font-semibold text-gray-900">{{ __('profile_edit_profile') }}</div>

                        <dl class="space-y-2 text-gray-600">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="font-medium text-gray-500">{{ __('auth_company_name') }}</dt>
                                <dd class="text-right">{{ Auth::guard('seller')->user()->company_name ?: __('dashboard_not_set') }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="font-medium text-gray-500">{{ __('auth_email') }}</dt>
                                <dd class="text-right break-all">{{ Auth::guard('seller')->user()->email }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="font-medium text-gray-500">{{ __('dashboard_member_since') }}</dt>
                                <dd class="text-right">{{ Auth::guard('seller')->user()->created_at->format('d F, Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </x-slot:content>
            </x-ui.popover>

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
                    <div class="flex items-center gap-3">
                        <x-ui.badge
                            :value="__('dashboard_bullish')"
                            color="success"
                            soft
                            sm
                        />
                        <x-ui.rating
                            model="marketTrendRating"
                            shape="circle"
                            color="success"
                            class="rating-sm"
                            disabled
                        />
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('dashboard_volatility') }}</span>
                    <div class="flex items-center gap-3">
                        <x-ui.badge
                            :value="__('dashboard_medium')"
                            color="warning"
                            soft
                            sm
                        />
                        <x-ui.rating
                            model="volatilityRating"
                            shape="circle"
                            color="warning"
                            class="rating-sm"
                            disabled
                        />
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">{{ __('dashboard_competition') }}</span>
                    <div class="flex items-center gap-3">
                        <x-ui.badge
                            :value="__('dashboard_high')"
                            color="error"
                            soft
                            sm
                        />
                        <x-ui.rating
                            model="competitionRating"
                            shape="circle"
                            color="error"
                            class="rating-sm"
                            disabled
                        />
                    </div>
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
        <div class="mb-4">
            <h3 class="text-lg font-semibold">{{ __('dashboard_monthly_sales') }}</h3>
            <p class="text-sm text-gray-500">{{ __('dashboard_monthly_sales_subtitle') }}</p>
        </div>

        <x-ui.chart wire:model="monthlySalesChart" class="h-80 rounded-lg bg-gray-50 p-4" />
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
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="grid flex-1 gap-3">
                    <x-ui.statistic
                        :title="__('dashboard_profit_factor')"
                        value="2.4"
                        icon="chart-bar-square"
                        color="text-info"
                        class="shadow-sm"
                    />

                    <x-ui.statistic
                        :title="__('dashboard_average_trade')"
                        value="+€234.56"
                        icon="arrow-trending-up"
                        color="text-success"
                        class="shadow-sm"
                    />

                    <x-ui.statistic
                        :title="__('dashboard_drawdown')"
                        value="-12.3%"
                        icon="arrow-trending-down"
                        color="text-error"
                        class="shadow-sm"
                    />
                </div>
                <div class="flex flex-col items-center rounded-2xl bg-base-200/70 px-6 py-5 text-center">
                    <x-ui.progress-radial
                        value="68"
                        color="success"
                        class="font-semibold"
                        style="--size: 5rem; --thickness: 0.45rem;"
                    />
                    <div class="mt-3 text-sm font-medium text-gray-900">{{ __('dashboard_win_rate') }}</div>
                    <x-ui.rating
                        model="winRateRating"
                        shape="circle"
                        color="success"
                        class="rating-sm mt-2"
                        disabled
                    />
                    <div class="text-xs text-gray-500">{{ __('dashboard_strong') }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">{{ __('dashboard_risk_management') }}</h3>
            <div class="space-y-4">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-4">
                        <span>{{ __('dashboard_position_size') }}</span>
                        <span class="text-sm font-medium">65%</span>
                    </div>
                    <x-ui.progress value="65" color="primary" class="h-2.5" />
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-4">
                        <span>{{ __('dashboard_risk_level') }}</span>
                        <span class="text-sm font-medium">45%</span>
                    </div>
                    <x-ui.progress value="45" color="warning" class="h-2.5" />
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-4">
                        <span>{{ __('dashboard_leverage') }}</span>
                        <span class="text-sm font-medium">30%</span>
                    </div>
                    <x-ui.progress value="30" color="error" class="h-2.5" />
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
                <div class="space-y-2">
                    <x-ui.progress value="65" color="success" class="h-2.5" />
                    <span class="text-sm font-medium text-gray-700">65%</span>
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
            <div class="space-y-2">
                <x-ui.progress value="45" color="primary" class="h-2.5" />
                <span class="text-sm font-medium text-gray-700">45%</span>
            </div>
            <div class="text-sm text-gray-500 mt-1">{{ __('dashboard_portfolio_description') }}</div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <h4 class="font-semibold mb-2">{{ __('dashboard_risk_assessment') }}</h4>
            <div class="space-y-3">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span>{{ __('dashboard_low_risk') }}</span>
                        <span class="font-medium">68%</span>
                    </div>
                    <x-ui.progress value="68" color="success" class="h-2.5" />
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span>{{ __('dashboard_medium_risk') }}</span>
                        <span class="font-medium">24%</span>
                    </div>
                    <x-ui.progress value="24" color="warning" class="h-2.5" />
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span>{{ __('dashboard_high_risk') }}</span>
                        <span class="font-medium">8%</span>
                    </div>
                    <x-ui.progress value="8" color="error" class="h-2.5" />
                </div>
            </div>
        </div>

    </div>
</div>
