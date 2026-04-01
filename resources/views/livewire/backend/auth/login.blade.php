<div class="min-h-[calc(100vh-3rem)] overflow-hidden rounded-[32px] border border-black/5 bg-[#f3efe7] shadow-[0_40px_120px_-60px_rgba(15,23,42,0.45)]">
    <div class="grid min-h-[calc(100vh-3rem)] lg:grid-cols-[1.15fr_0.85fr]">
        <section class="relative overflow-hidden bg-[#13261f] px-6 py-8 text-white sm:px-8 sm:py-10 lg:px-12 lg:py-12">
            <div class="absolute inset-0 opacity-80">
                <div class="absolute -left-20 top-12 h-52 w-52 rounded-full bg-[#d2ff72]/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-[#f4c16d]/10 blur-3xl"></div>
                <div class="absolute inset-y-0 right-24 w-px bg-white/10"></div>
                <div class="absolute left-0 top-24 h-px w-full bg-white/10"></div>
            </div>

            <div class="relative z-10 flex h-full flex-col justify-between gap-10">
                <div class="inline-flex w-fit items-center gap-3 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/85">
                    <span class="h-2 w-2 rounded-full bg-[#d2ff72]"></span>
                    {{ config('app.name') }}
                </div>

                <div class="max-w-xl space-y-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.38em] text-white/55">
                        {{ __('common_sign_in') }}
                    </p>

                    <div class="space-y-4">
                        <h1 class="text-4xl font-black uppercase leading-none sm:text-5xl xl:text-6xl">
                            {{ config('app.name') }}
                        </h1>
                        <div class="h-1 w-24 rounded-full bg-[#d2ff72]"></div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm text-white/80">
                            {{ __('common_email_address') }}
                        </div>
                        <div class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm text-white/80">
                            {{ __('common_password') }}
                        </div>
                        <div class="rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm text-white/80">
                            {{ __('common_remember_me') }}
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 text-sm text-white/65 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <div class="mb-2 h-1.5 w-12 rounded-full bg-[#d2ff72]"></div>
                        <div>{{ __('common_email_address') }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <div class="mb-2 h-1.5 w-12 rounded-full bg-[#f4c16d]"></div>
                        <div>{{ __('common_password') }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                        <div class="mb-2 h-1.5 w-12 rounded-full bg-white/70"></div>
                        <div>{{ __('common_sign_in') }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative flex items-center bg-[#f7f4ee] px-6 py-8 sm:px-8 sm:py-10 lg:px-12">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#7a6c57]">
                        {{ config('app.name') }}
                    </p>
                    <h2 class="mt-4 text-3xl font-black tracking-tight text-[#181510] sm:text-4xl">
                        {{ __('common_sign_in') }}
                    </h2>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form wire:submit.prevent="login" x-data="{ showPassword: false }" class="space-y-5">
                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-[#3f3426]">
                            {{ __('common_email_address') }}
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#8a7b66]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4 7 8 6 8-6" />
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                autocomplete="email"
                                wire:model="email"
                                class="block w-full rounded-2xl border border-[#d7cfbf] bg-white py-4 pl-12 pr-4 text-[#181510] shadow-sm outline-none transition focus:border-[#13261f] focus:ring-4 focus:ring-[#13261f]/10 @error('email') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-[#3f3426]">
                            {{ __('common_password') }}
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#8a7b66]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 11V8a5 5 0 0 1 10 0v3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 11h14v9H5z" />
                                </svg>
                            </span>
                            <input
                                id="password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                wire:model="password"
                                class="block w-full rounded-2xl border border-[#d7cfbf] bg-white py-4 pl-12 pr-14 text-[#181510] shadow-sm outline-none transition focus:border-[#13261f] focus:ring-4 focus:ring-[#13261f]/10 @error('password') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                            >
                            <button
                                type="button"
                                x-on:click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-[#7a6c57] transition hover:text-[#13261f]"
                            >
                                <svg x-show="!showPassword" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
                                    <circle cx="12" cy="12" r="3" stroke-width="1.8"></circle>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 3 18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.6 10.6A3 3 0 0 0 13.4 13.4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.88 5.09A10.94 10.94 0 0 1 12 4.9c6 0 9.5 7.1 9.5 7.1a14.2 14.2 0 0 1-3.08 3.77" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.23 6.23A14.16 14.16 0 0 0 2.5 12s3.5 7.1 9.5 7.1a10.9 10.9 0 0 0 5.77-1.73" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl border border-[#d7cfbf] bg-white px-4 py-4 text-sm text-[#3f3426]">
                        <input
                            id="remember"
                            type="checkbox"
                            wire:model="remember"
                            class="h-4 w-4 rounded border-[#b8aa93] text-[#13261f] focus:ring-[#13261f]"
                        >
                        <span>{{ __('common_remember_me') }}</span>
                    </label>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-3 rounded-2xl bg-[#13261f] px-5 py-4 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#0d1b16] disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"></circle>
                            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" class="opacity-90"></path>
                        </svg>
                        <span>{{ __('common_sign_in') }}</span>
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
