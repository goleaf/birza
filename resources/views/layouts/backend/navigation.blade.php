<nav class="bg-base-100 shadow">
    @auth('admin')
        @php
            $adminUser = Auth::guard('admin')->user();
            $adminInitials = \Illuminate\Support\Str::of((string) $adminUser?->name)
                ->explode(' ')
                ->filter()
                ->take(2)
                ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                ->implode('');

            $primaryNavigationItems = [
                [
                    'title' => __('navigation_countries'),
                    'link' => route('admin.countries.index'),
                    'icon' => 'o-globe-europe-africa',
                    'can' => ['viewAny', \App\Models\Country::class],
                ],
                [
                    'title' => __('navigation_categories'),
                    'link' => route('admin.categories.index'),
                    'icon' => 'o-squares-2x2',
                    'can' => ['viewAny', \App\Models\Category::class],
                ],
                [
                    'title' => __('navigation_products'),
                    'link' => route('admin.products.index'),
                    'icon' => 'o-cube',
                    'can' => ['viewAny', \App\Models\Product::class],
                ],
                [
                    'title' => __('bundles.title'),
                    'link' => route('admin.bundles.index'),
                    'icon' => 'o-rectangle-stack',
                    'can' => ['viewAny', \App\Models\ProductBundle::class],
                ],
                [
                    'title' => __('products.questions.nav_admin'),
                    'link' => route('admin.product-questions.index'),
                    'icon' => 'o-question-mark-circle',
                    'can' => ['viewAny', \App\Models\ProductQuestion::class],
                ],
                [
                    'title' => __('navigation_product_reports'),
                    'link' => route('admin.reports.index'),
                    'icon' => 'o-shield-exclamation',
                    'can' => ['viewAny', \App\Models\ProductReport::class],
                ],
                [
                    'title' => __('navigation_sellers'),
                    'link' => route('admin.sellers.index'),
                    'icon' => 'o-building-storefront',
                    'can' => ['viewAny', \App\Models\Users\Seller::class],
                ],
                [
                    'title' => __('navigation_buyers'),
                    'link' => route('admin.buyers.index'),
                    'icon' => 'o-users',
                    'can' => ['viewAny', \App\Models\Users\Buyer::class],
                ],
                [
                    'title' => __('navigation_orders'),
                    'link' => route('admin.orders.index'),
                    'icon' => 'o-shopping-bag',
                    'can' => ['viewAny', \App\Models\Order::class],
                ],
                [
                    'title' => __('messages.admin_title'),
                    'link' => route('admin.messages.index'),
                    'icon' => 'o-chat-bubble-left-right',
                    'can' => ['viewAny', \App\Models\Conversation::class],
                ],
            ];

            $attributeNavigationItems = [
                [
                    'title' => __('navigation_attributes_list'),
                    'link' => route('admin.attributes.index'),
                    'icon' => 'o-list-bullet',
                    'can' => ['viewAny', \App\Models\Attribute::class],
                ],
                [
                    'title' => __('navigation_create_attribute'),
                    'link' => route('admin.attributes.create'),
                    'icon' => 'o-plus-circle',
                    'can' => ['create', \App\Models\Attribute::class],
                ],
            ];

            $primaryNavigationItems = collect($primaryNavigationItems)
                ->filter(fn (array $item): bool => \Illuminate\Support\Facades\Gate::forUser($adminUser)->allows($item['can'][0], $item['can'][1]))
                ->values();

            $attributeNavigationItems = collect($attributeNavigationItems)
                ->filter(fn (array $item): bool => \Illuminate\Support\Facades\Gate::forUser($adminUser)->allows($item['can'][0], $item['can'][1]))
                ->values();

            $canManageSettings = \Illuminate\Support\Facades\Gate::forUser($adminUser)->allows('manage', \App\Models\GlobalSettings::class);
            $canViewAuditLogs = \Illuminate\Support\Facades\Gate::forUser($adminUser)->allows('viewAny', \App\Models\AuditLog::class);
        @endphp

        <div class="navbar px-4 lg:px-6">
            <div class="navbar-start">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="btn btn-ghost text-lg font-semibold"
                    style="color: var(--admin-primary);"
                >
                    {{ config('app.name') }}
                </a>
            </div>

            <div class="navbar-center hidden lg:flex">
                <x-mary-menu
                    activate-by-route
                    active-bg-color="bg-base-200 font-semibold text-base-content"
                    class="menu-horizontal !w-auto flex-nowrap gap-1 rounded-2xl border border-base-200 bg-base-100/80 px-2 py-1 shadow-sm backdrop-blur"
                >
                    @foreach ($primaryNavigationItems as $item)
                        <x-mary-menu-item
                            :title="$item['title']"
                            :link="$item['link']"
                            :icon="$item['icon']"
                        />
                    @endforeach

                    @if ($attributeNavigationItems->isNotEmpty())
                        <x-mary-menu-sub :title="__('navigation_attributes')" icon="o-adjustments-horizontal">
                            @foreach ($attributeNavigationItems as $item)
                                <x-mary-menu-item
                                    :title="$item['title']"
                                    :link="$item['link']"
                                    :icon="$item['icon']"
                                />
                            @endforeach
                        </x-mary-menu-sub>
                    @endif

                    @if ($canManageSettings)
                        <x-mary-menu-item
                            :title="__('navigation_global_settings')"
                            :link="route('admin.settings.index')"
                            icon="o-cog-6-tooth"
                        />
                    @endif

                    @if ($canViewAuditLogs)
                        <x-mary-menu-item
                            :title="__('audit_logs.navigation')"
                            :link="route('admin.audit.index')"
                            icon="o-clipboard-document-check"
                        />
                    @endif
                </x-mary-menu>
            </div>

            <div class="navbar-end gap-2">
                @if (($notificationDropdown['indexRoute'] ?? null) !== null)
                    <x-notifications.dropdown
                        :notifications="$notificationDropdown['notifications']"
                        :unread-count="$notificationDropdown['unreadCount']"
                        :index-route="$notificationDropdown['indexRoute']"
                        :mark-read-route-name="$notificationDropdown['markReadRouteName']"
                        :mark-all-route="$notificationDropdown['markAllRoute']"
                    />
                @endif

                <button
                    type="button"
                    class="btn btn-ghost btn-sm gap-2"
                    style="color: var(--admin-primary);"
                    @click.stop="$dispatch('mary-search-open')"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.85-4.9a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                    </svg>
                    <span class="hidden xl:inline">{{ __('backend_spotlight_open') }}</span>
                    <span class="badge badge-outline hidden 2xl:inline">{{ __('backend_spotlight_shortcut') }}</span>
                </button>

                <x-mary-dropdown right no-x-anchor>
                    <x-slot:trigger class="list-none">
                        <span class="btn btn-ghost btn-sm gap-2">
                            <x-mary-icon name="o-language" class="h-4 w-4" />
                            <span>{{ strtoupper(app()->getLocale()) }}</span>
                            <x-mary-icon name="o-chevron-down" class="h-4 w-4" />
                        </span>
                    </x-slot:trigger>

                    <x-mary-menu class="!w-40">
                        @foreach (config('app.locales') as $locale)
                            <x-mary-menu-item
                                :title="strtoupper($locale)"
                                :link="route('language.switch', $locale)"
                                icon="o-language"
                                :active="app()->getLocale() === $locale"
                                exact
                            />
                        @endforeach
                    </x-mary-menu>
                </x-mary-dropdown>

                <x-mary-dropdown right class="btn btn-ghost btn-sm" no-x-anchor>
                    <x-slot:trigger class="btn btn-ghost btn-sm gap-2 pr-1">
                        <x-mary-avatar
                            :placeholder="$adminInitials"
                            :alt="$adminUser->name"
                            class="!w-8"
                        />
                        <span class="hidden xl:inline">{{ $adminUser->name }}</span>
                        <x-mary-icon name="o-chevron-down" class="h-4 w-4" />
                    </x-slot:trigger>

                    <x-mary-menu
                        activate-by-route
                        active-bg-color="bg-base-200 font-semibold text-base-content"
                        class="!w-56"
                    >
                        <x-mary-menu-item
                            :title="__('navigation_profile')"
                            :link="route('admin.profile')"
                            icon="o-user-circle"
                        />

                        <x-mary-menu-separator />

                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="my-0.5 flex w-full items-center gap-3 whitespace-nowrap rounded-lg px-4 py-1.5 text-left hover:bg-base-200"
                                >
                                    <x-mary-icon name="o-arrow-left-on-rectangle" class="h-4 w-4" />
                                    <span>{{ __('navigation_logout') }}</span>
                                </button>
                            </form>
                        </li>
                    </x-mary-menu>
                </x-mary-dropdown>
            </div>
        </div>

        <div class="border-t border-base-200 px-4 py-2 lg:hidden">
            <div class="mb-3">
                <button
                    type="button"
                    class="btn btn-outline btn-sm w-full justify-between"
                    style="color: var(--admin-primary); border-color: var(--admin-primary);"
                    @click.stop="$dispatch('mary-search-open')"
                >
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.85-4.9a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z" />
                        </svg>
                        {{ __('backend_spotlight_open') }}
                    </span>
                    <span class="badge badge-outline">{{ __('backend_spotlight_shortcut') }}</span>
                </button>
            </div>

            <x-mary-menu
                activate-by-route
                active-bg-color="bg-base-200 font-semibold text-base-content"
                class="rounded-2xl border border-base-200 bg-base-100/90 p-1 shadow-sm"
            >
                @foreach ($primaryNavigationItems as $item)
                    <x-mary-menu-item
                        :title="$item['title']"
                        :link="$item['link']"
                        :icon="$item['icon']"
                    />
                @endforeach

                @if ($attributeNavigationItems->isNotEmpty())
                    <x-mary-menu-sub :title="__('navigation_attributes')" icon="o-adjustments-horizontal">
                        @foreach ($attributeNavigationItems as $item)
                            <x-mary-menu-item
                                :title="$item['title']"
                                :link="$item['link']"
                                :icon="$item['icon']"
                            />
                        @endforeach
                    </x-mary-menu-sub>
                @endif

                @if ($canManageSettings)
                    <x-mary-menu-item
                        :title="__('navigation_global_settings')"
                        :link="route('admin.settings.index')"
                        icon="o-cog-6-tooth"
                    />
                @endif

                @if ($canViewAuditLogs)
                    <x-mary-menu-item
                        :title="__('audit_logs.navigation')"
                        :link="route('admin.audit.index')"
                        icon="o-clipboard-document-check"
                    />
                @endif
            </x-mary-menu>
        </div>
    @endauth
</nav>
