<!-- start main header -->
<div class="bg-white shadow">
    <!-- start header container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- start header content -->
        <div class="flex justify-between h-16">
            <!-- start left side -->
            <div class="flex items-center">
                @if ($guard)
                    <!-- start dashboard link -->
                    <a 
                        href="{{ route($guard . '.dashboard') }}" 
                        class="flex items-center space-x-2"
                    >
                        <x-ui.icon name="cube-transparent" class="mr-2 h-8 w-8 text-blue-500" />
                        <h1 class="text-2xl font-bold text-gray-900">
                            imk24.lt
                        </h1>
                    </a>
                    <!-- end dashboard link -->
                @else
                    <!-- start home link -->
                    <a 
                        href="{{ route('home') }}" 
                        class="flex items-center space-x-2"
                    >
                        <x-ui.icon name="cube-transparent" class="mr-2 h-8 w-8 text-blue-500" />
                        <h1 class="text-2xl font-bold text-gray-900">
                            imk24.lt
                        </h1>
                    </a>
                    <!-- end home link -->
                @endif

                <!-- start language switcher -->
                <div class="ml-8 flex items-center space-x-2">
                    @foreach (config('app.locales') as $lang)
                        <!-- start language link -->
                        <a 
                            href="{{ route('language.switch', $lang) }}"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ app()->getLocale() == $lang ? 'bg-blue-600 text-white shadow-md' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600' }}"
                        >
                            {{ strtoupper($lang) }}
                        </a>
                        <!-- end language link -->
                    @endforeach
                </div>
                <!-- end language switcher -->
            </div>
            <!-- end left side -->

            <!-- start right side -->
            <div class="flex items-center space-x-6">
                @if ($guard)
                    <!-- start user menu -->
                    <div class="flex items-center space-x-4">
                        <!-- start company name -->
                        <x-ui.popover position="bottom-end">
                            <x-slot:trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1.5 text-sm text-gray-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                                >
                                    <x-ui.icon name="building-office-2" class="h-4 w-4" />
                                    <span>{{ $user->company_name ?: $user->name }}</span>
                                </button>
                            </x-slot:trigger>

                            <x-slot:content>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                            <x-ui.icon name="user-circle" class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $guard === 'buyer' ? __('buyer_role_name') : __('seller_role_name') }}</div>
                                        </div>
                                    </div>

                                    <dl class="space-y-2 text-gray-600">
                                        <div class="flex items-start justify-between gap-3">
                                            <dt class="font-medium text-gray-500">{{ __('auth_company_name') }}</dt>
                                            <dd class="text-right">{{ $user->company_name ?: __('dashboard_not_set') }}</dd>
                                        </div>
                                        <div class="flex items-start justify-between gap-3">
                                            <dt class="font-medium text-gray-500">{{ __('auth_email') }}</dt>
                                            <dd class="text-right break-all">{{ $user->email }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </x-slot:content>
                        </x-ui.popover>
                        <!-- end company name -->

                        <!-- start navigation -->
                        <nav class="flex space-x-3">
                            @if ($guard == 'buyer')
                                @php($cartItemsCount = LaraCart::count())
                                <!-- start cart link -->
                                <a 
                                    href="{{ route('buyer.cart.index') }}"
                                    class="inline-flex items-center gap-2 transition-colors {{ $cartItemsCount > 0 ? 'text-blue-600' : 'text-gray-700 hover:text-blue-500' }}"
                                >
                                    {{ __('common_cart') }}
                                    <x-ui.badge :value="(string) $cartItemsCount" color="primary" sm />
                                </a>
                                <!-- end cart link -->
                            @endif

                            <!-- start dashboard link -->
                            <a 
                                href="{{ route($guard . '.dashboard') }}"
                                class="text-gray-700 hover:text-blue-500 transition-colors"
                            >
                                {{ __('dashboard_title') }}
                            </a>
                            <!-- end dashboard link -->

                            <!-- start profile link -->
                            <a 
                                href="{{ route($guard . '.profile.edit') }}"
                                class="text-gray-700 hover:text-blue-500 transition-colors"
                            >
                                {{ __('profile_edit_profile') }}
                            </a>
                            <!-- end profile link -->

                            <!-- start logout form -->
                            <form 
                                method="POST" 
                                action="{{ route($guard . '.logout') }}" 
                                class="inline"
                            >
                                @csrf
                                <button 
                                    type="submit" 
                                    class="text-gray-700 hover:text-red-500 transition-colors"
                                >
                                    {{ __('auth_logout') }}
                                </button>
                            </form>
                            <!-- end logout form -->
                        </nav>
                        <!-- end navigation -->
                    </div>
                    <!-- end user menu -->
                @endif
            </div>
            <!-- end right side -->
        </div>
        <!-- end header content -->
    </div>
    <!-- end header container -->
</div>
<!-- end main header -->
