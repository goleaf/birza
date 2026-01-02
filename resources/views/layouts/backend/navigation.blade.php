<nav class="navbar bg-base-100 shadow">
    @auth('admin')
        @php
            $adminPrefix = 'admin';
            $navLinks = [
                [
                    'href' => url($adminPrefix . '/countries'),
                    'label' => __('navigation.countries'),
                    'active' => request()->is($adminPrefix . '/countries*'),
                ],
                [
                    'href' => url($adminPrefix . '/categories'),
                    'label' => __('navigation.categories'),
                    'active' => request()->is($adminPrefix . '/categories*'),
                ],
                [
                    'href' => url($adminPrefix . '/products'),
                    'label' => __('navigation.products'),
                    'active' => request()->is($adminPrefix . '/products*'),
                ],
                [
                    'href' => url($adminPrefix . '/sellers'),
                    'label' => __('navigation.sellers'),
                    'active' => request()->is($adminPrefix . '/sellers*'),
                ],
                [
                    'href' => url($adminPrefix . '/buyers'),
                    'label' => __('navigation.buyers'),
                    'active' => request()->is($adminPrefix . '/buyers*'),
                ],
                [
                    'href' => url($adminPrefix . '/orders'),
                    'label' => __('navigation.orders'),
                    'active' => request()->is($adminPrefix . '/orders*'),
                ],
                [
                    'href' => url($adminPrefix . '/settings'),
                    'label' => __('navigation.global_settings'),
                    'active' => request()->is($adminPrefix . '/settings*'),
                ],
            ];
            $attributesActive = request()->is($adminPrefix . '/attributes*');
        @endphp

        <div class="navbar-start">
            <div class="dropdown">
                <label tabindex="0" class="btn btn-ghost lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>
                <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 w-64 rounded-box bg-base-100 p-2 shadow">
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
