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
                        <svg class="w-8 h-8 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9-4 9 4v12l-9 4-9-4V6z" /></svg>
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
                        <svg class="w-8 h-8 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9-4 9 4v12l-9 4-9-4V6z" /></svg>
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
                        <span class="text-gray-600">
                            {{ $user->company_name }}
                        </span>
                        <!-- end company name -->

                        <!-- start navigation -->
                        <nav class="flex space-x-3">
                            @if ($guard == 'buyer')
                                <!-- start cart link -->
                                <a 
                                    href="{{ route('buyer.cart.index') }}"
                                    class="transition-colors {{ LaraCart::count() > 0 ? 'bg-blue-500 text-white px-3 py-1 rounded-full' : 'text-gray-700 hover:text-blue-500' }}"
                                >
                                    {{ __('common.cart') }} ({{ LaraCart::count() }})
                                </a>
                                <!-- end cart link -->
                            @endif

                            <!-- start dashboard link -->
                            <a 
                                href="{{ route($guard . '.dashboard') }}"
                                class="text-gray-700 hover:text-blue-500 transition-colors"
                            >
                                {{ __('dashboard.title') }}
                            </a>
                            <!-- end dashboard link -->

                            <!-- start profile link -->
                            <a 
                                href="{{ route($guard . '.profile.edit') }}"
                                class="text-gray-700 hover:text-blue-500 transition-colors"
                            >
                                {{ __('profile.edit_profile') }}
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
                                    {{ __('auth.logout') }}
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
