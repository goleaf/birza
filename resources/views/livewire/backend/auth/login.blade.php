<div class="mx-auto grid w-full max-w-6xl gap-6 overflow-hidden rounded-[32px] border border-black/5 bg-base-100/90 shadow-[0_40px_120px_-60px_rgba(15,23,42,0.45)] lg:grid-cols-[1.08fr_0.92fr]">
    <section class="relative overflow-hidden bg-[#13261f] px-6 py-8 text-white sm:px-8 sm:py-10 lg:px-12 lg:py-12">
        <div class="absolute inset-0 opacity-80">
            <div class="absolute -left-16 top-12 h-48 w-48 rounded-full bg-[#d2ff72]/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-[#f4c16d]/10 blur-3xl"></div>
            <div class="absolute inset-y-0 right-20 w-px bg-white/10"></div>
            <div class="absolute left-0 top-24 h-px w-full bg-white/10"></div>
        </div>

        <div class="relative z-10 flex h-full flex-col justify-between gap-10">
            <div class="inline-flex w-fit items-center gap-3 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/85">
                <span class="h-2 w-2 rounded-full bg-[#d2ff72]"></span>
                {{ __('backend_auth_portal_badge') }}
            </div>

            <div class="max-w-xl space-y-6">
                <p class="text-xs font-semibold uppercase tracking-[0.38em] text-white/55">
                    {{ config('app.name') }}
                </p>

                <div class="space-y-4">
                    <h1 class="text-4xl font-black uppercase leading-none sm:text-5xl xl:text-6xl">
                        {{ __('backend_auth_portal_title') }}
                    </h1>
                    <div class="h-1 w-24 rounded-full bg-[#d2ff72]"></div>
                </div>

                <p class="max-w-lg text-sm leading-7 text-white/75 sm:text-base">
                    {{ __('backend_auth_portal_copy') }}
                </p>

                <div class="flex flex-wrap gap-3">
                    <div class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm text-white/80">
                        {{ __('navigation_products') }}
                    </div>
                    <div class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm text-white/80">
                        {{ __('navigation_orders') }}
                    </div>
                    <div class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm text-white/80">
                        {{ __('navigation_global_settings') }}
                    </div>
                </div>
            </div>

            <div class="grid gap-3 text-sm text-white/65 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                    <div class="mb-2 h-1.5 w-12 rounded-full bg-[#d2ff72]"></div>
                    <div>{{ __('navigation_products') }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                    <div class="mb-2 h-1.5 w-12 rounded-full bg-[#f4c16d]"></div>
                    <div>{{ __('navigation_orders') }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                    <div class="mb-2 h-1.5 w-12 rounded-full bg-white/70"></div>
                    <div>{{ __('navigation_sellers') }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="flex items-center px-6 py-8 sm:px-8 sm:py-10 lg:px-12">
        <div class="mx-auto w-full max-w-md space-y-8">
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#7a6c57]">
                    {{ config('app.name') }}
                </p>

                <div>
                    <h2 class="text-3xl font-black tracking-tight text-[#181510] sm:text-4xl">
                        {{ __('common_sign_in') }}
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-[#6f624e]">
                        {{ __('backend_auth_login_hint') }}
                    </p>
                </div>
            </div>

            <x-mary-form wire:submit="login" no-separator class="gap-6">
                <x-mary-input
                    :label="__('common_email_address')"
                    wire:model="email"
                    type="email"
                    icon="o-envelope"
                    clearable
                    autocomplete="email"
                    required
                    autofocus
                />

                <x-mary-password
                    :label="__('common_password')"
                    wire:model="password"
                    right
                    clearable
                    autocomplete="current-password"
                    required
                />

                <x-mary-checkbox
                    :label="__('common_remember_me')"
                    wire:model="remember"
                />

                <x-slot:actions>
                    <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-[#7a6c57]">
                            {{ __('backend_auth_authorized_only') }}
                        </p>

                        <x-mary-button
                            :label="__('common_sign_in')"
                            icon="o-paper-airplane"
                            spinner="login"
                            type="submit"
                            class="btn-primary"
                        />
                    </div>
                </x-slot:actions>
            </x-mary-form>
        </div>
    </section>
</div>
