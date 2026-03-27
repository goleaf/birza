<nav class="bg-base-100 shadow">
    @auth('admin')
        <div class="navbar px-4 lg:px-6">
            <div class="navbar-start">
                <a href="{{ route('backend.dashboard') }}" class="btn btn-ghost text-lg">
                    {{ config('app.name') }}
                </a>
            </div>

            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal gap-1 px-1">
                    <li>
                        <a href="{{ route('backend.countries.index') }}" class="{{ request()->routeIs('backend.countries.*') ? 'active' : '' }}">
                            {{ __('navigation_countries') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backend.categories.index') }}" class="{{ request()->routeIs('backend.categories.*') ? 'active' : '' }}">
                            {{ __('navigation_categories') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backend.products.index') }}" class="{{ request()->routeIs('backend.products.*') ? 'active' : '' }}">
                            {{ __('navigation_products') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backend.sellers.index') }}" class="{{ request()->routeIs('backend.sellers.*') ? 'active' : '' }}">
                            {{ __('navigation_sellers') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backend.buyers.index') }}" class="{{ request()->routeIs('backend.buyers.*') ? 'active' : '' }}">
                            {{ __('navigation_buyers') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('backend.orders.index') }}" class="{{ request()->routeIs('backend.orders.*') ? 'active' : '' }}">
                            {{ __('navigation_orders') }}
                        </a>
                    </li>
                    <li>
                        <details {{ request()->routeIs('backend.attributes.*') ? 'open' : '' }}>
                            <summary class="{{ request()->routeIs('backend.attributes.*') ? 'active' : '' }}">
                                {{ __('navigation_attributes') }}
                            </summary>
                            <ul class="p-2">
                                <li>
                                    <a href="{{ route('backend.attributes.index') }}" class="{{ request()->routeIs('backend.attributes.index') ? 'active' : '' }}">
                                        {{ __('navigation_attributes_list') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('backend.attributes.create') }}" class="{{ request()->routeIs('backend.attributes.create') ? 'active' : '' }}">
                                        {{ __('navigation_create_attribute') }}
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                    <li>
                        <a href="{{ route('backend.settings.index') }}" class="{{ request()->routeIs('backend.settings.*') ? 'active' : '' }}">
                            {{ __('navigation_global_settings') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="navbar-end gap-2">
                <div role="tablist" class="tabs tabs-boxed">
                    @foreach (config('app.locales') as $locale)
                        <a
                            role="tab"
                            href="{{ route('language.switch', $locale) }}"
                            class="tab {{ app()->getLocale() === $locale ? 'tab-active' : '' }}"
                        >
                            {{ strtoupper($locale) }}
                        </a>
                    @endforeach
                </div>

                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm">
                        <span>{{ Auth::guard('admin')->user()->name }}</span>
                        <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 w-44 rounded-box bg-base-100 p-2 shadow">
                        <li>
                            <a href="{{ route('backend.admin.profile') }}">
                                {{ __('navigation_profile') }}
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('backend.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left">
                                    {{ __('navigation_logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-base-200 px-4 py-2 lg:hidden">
            <div class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('backend.countries.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.countries.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_countries') }}
                </a>
                <a href="{{ route('backend.categories.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.categories.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_categories') }}
                </a>
                <a href="{{ route('backend.products.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.products.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_products') }}
                </a>
                <a href="{{ route('backend.sellers.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.sellers.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_sellers') }}
                </a>
                <a href="{{ route('backend.buyers.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.buyers.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_buyers') }}
                </a>
                <a href="{{ route('backend.orders.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.orders.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_orders') }}
                </a>
                <a href="{{ route('backend.attributes.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.attributes.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_attributes') }}
                </a>
                <a href="{{ route('backend.settings.index') }}" class="btn btn-ghost btn-sm {{ request()->routeIs('backend.settings.*') ? 'btn-active' : '' }}">
                    {{ __('navigation_global_settings') }}
                </a>
            </div>
        </div>
    @endauth
</nav>
