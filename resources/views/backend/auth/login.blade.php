@extends('layouts.backend.app')

@section('content')
<x-ui.auth-card
    full-screen
    background-class="bg-gradient-to-br from-base-200 via-base-100 to-base-200"
    max-width-class="w-full"
    :title="__('common.sign_in')"
>
    <form class="space-y-4" method="POST" action="{{ route('backend.login') }}" x-data="{ showPassword: false }">
        @csrf

        <div class="form-control">
            <label for="email" class="label">
                <span class="label-text">{{ __('common.email_address') }}</span>
            </label>
            <input id="email" name="email" type="email" autocomplete="email" required
                class="input input-bordered w-full @error('email') input-error @enderror"
                value="{{ old('email') }}"
            >
            @error('email')
                <span class="mt-1 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-control">
            <label for="password" class="label">
                <span class="label-text">{{ __('common.password') }}</span>
            </label>
            <div class="relative">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password"
                    autocomplete="current-password" required
                    class="input input-bordered w-full pr-12 @error('password') input-error @enderror"
                >
                <button type="button"
                    @click="showPassword = !showPassword"
                    class="btn btn-ghost btn-xs absolute right-2 top-1/2 -translate-y-1/2"
                >
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            @error('password')
                <span class="mt-1 text-sm text-error">{{ $message }}</span>
            @enderror
        </div>

        <label class="label cursor-pointer justify-start gap-2">
            <input id="remember" name="remember" type="checkbox" class="checkbox checkbox-primary" />
            <span class="label-text">{{ __('common.remember_me') }}</span>
        </label>

        <button type="submit" class="btn btn-primary w-full">
            {{ __('common.sign_in') }}
        </button>
    </form>
</x-ui.auth-card>
@endsection
