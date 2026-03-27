<nav class="navbar bg-base-100 shadow">
    @auth('admin')
    <!-- start navigation container -->
    <div class="flex items-center justify-between h-16">
        <!-- start left menu -->
        <div class="flex items-center">
            <div class="ml-10 flex items-baseline space-x-4">
                <!-- start countries link -->
                <a 
                    href="{{ route('backend.countries.index') }}"
                    class="{{ request()->routeIs('backend.countries.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium"
                >
                    {{ __('navigation_countries') }}
                </a>
                <!-- end countries link -->

                <!-- start categories link -->
                <a 
                    href="{{ route('backend.categories.index') }}"
                    class="{{ request()->routeIs('backend.categories.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium"
                >
                    {{ __('navigation_categories') }}
                </a>
                <!-- end categories link -->

                <!-- start products link -->
                <a 
                    href="{{ route('backend.products.index') }}"
                    class="{{ request()->routeIs('backend.products.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium"
                >
                    {{ __('navigation_products') }}
                </a>
                <!-- end products link -->

                <!-- start sellers link -->
                <a 
                    href="{{ route('backend.sellers.index') }}"
                    class="{{ request()->routeIs('backend.sellers.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium"
                >
                    {{ __('navigation_sellers') }}
                </a>
                <!-- end sellers link -->

                <!-- start buyers link -->
                <a 
                    href="{{ route('backend.buyers.index') }}"
                    class="{{ request()->routeIs('backend.buyers.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium"
                >
                    {{ __('navigation_buyers') }}
                </a>
                <!-- end buyers link -->

                <!-- start orders link -->
                <a 
                    href="{{ route('backend.orders.index') }}"
                    class="{{ request()->routeIs('backend.orders.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium"
                >
                    {{ __('navigation_orders') }}
                </a>
                <!-- end orders link -->

                <!-- start attributes dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <!-- start attributes button -->
                    <button 
                        @click="open = !open" 
                        @click.away="open = false"
                        class="{{ request()->routeIs('backend.attributes.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium flex items-center"
                    >
                        <span>{{ __('navigation_attributes') }}</span>
                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <!-- end attributes button -->

                    <!-- start attributes dropdown menu -->
                    <div 
                        x-show="open"
                        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5"
                    >
                        <a 
                            href="{{ route('backend.attributes.index') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                            {{ __('navigation_attributes_list') }}
                        </a>
                        <a 
                            href="{{ route('backend.attributes.create') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        >
                            {{ __('navigation_create_attribute') }}
                        </a>
                    </div>
                    <!-- end attributes dropdown menu -->
                </div>
                <!-- end attributes dropdown -->

                <!-- start global settings link -->
                <a 
                    href="{{ route('backend.settings.index') }}"
                    class="{{ request()->routeIs('backend.settings.*') ? 'text-white bg-gray-700' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} px-3 py-2 rounded-md text-sm font-medium"
                >
                    {{ __('navigation_global_settings') }}
                </a>
                <!-- end global settings link -->
            </div>
        </div>
        <!-- end left menu -->

        <!-- start right menu -->
        <div class="flex items-center pr-4 space-x-4">
            <!-- Language Selector -->
            <div class="relative">
                <select onchange="window.location.href=this.value" class="block appearance-none bg-gray-700 text-white border border-gray-600 rounded-md py-2 pl-3 pr-8 text-sm leading-5">
                    @foreach(config('app.locales') as $locale)
                        <option value="{{ route('language.switch', $locale) }}" {{ app()->getLocale() == $locale ? 'selected' : '' }}>
                            {{ strtoupper($locale) }}
                        </option>
                    @endforeach
                    <li>
                        <details {{ $attributesActive ? 'open' : '' }}>
                            <summary class="{{ $attributesActive ? 'active' : '' }}">
                                {{ __('navigation.attributes') }}
                            </summary>
                            <ul class="p-2">
                                <li>
                                    <a href="{{ url($adminPrefix . '/attributes') }}" class="{{ request()->is($adminPrefix . '/attributes') ? 'active' : '' }}">
                                        {{ __('navigation.attributes_list') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url($adminPrefix . '/attributes/create') }}" class="{{ request()->is($adminPrefix . '/attributes/create') ? 'active' : '' }}">
                                        {{ __('navigation.create_attribute') }}
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                </ul>
            </div>
            <a href="{{ url($adminPrefix) }}" class="btn btn-ghost text-lg">
                {{ config('app.name') }}
            </a>
        </div>

        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                @foreach ($navLinks as $link)
                    <li>
                        <a href="{{ $link['href'] }}" class="{{ $link['active'] ? 'active' : '' }}">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
                <li>
                    <details {{ $attributesActive ? 'open' : '' }}>
                        <summary class="{{ $attributesActive ? 'active' : '' }}">
                            {{ __('navigation.attributes') }}
                        </summary>
                        <ul class="p-2">
                            <li>
                                <a href="{{ url($adminPrefix . '/attributes') }}" class="{{ request()->is($adminPrefix . '/attributes') ? 'active' : '' }}">
                                    {{ __('navigation.attributes_list') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ url($adminPrefix . '/attributes/create') }}" class="{{ request()->is($adminPrefix . '/attributes/create') ? 'active' : '' }}">
                                    {{ __('navigation.create_attribute') }}
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
        </div>

        <div class="navbar-end gap-2">
            <div role="tablist" class="tabs tabs-boxed">
                @foreach (config('app.locales') as $locale)
                    <a
                        role="tab"
                        href="{{ route('language.switch', $locale) }}"
                        class="tab {{ app()->getLocale() == $locale ? 'tab-active' : '' }}"
                    >
                        {{ strtoupper($locale) }}
                    </a>
                @endforeach
            </div>

            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-sm">
                    @auth('admin')
                        <span>{{ Auth::guard('admin')->user()->name }}</span>
                    @endauth
                    <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </label>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 w-44 rounded-box bg-base-100 p-2 shadow">
                    <li>
                        <a href="{{ url($adminPrefix . '/profile') }}">
                            {{ __('navigation.profile') }}
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ url($adminPrefix . '/logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left">
                                {{ __('navigation.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    @endauth
</nav>
