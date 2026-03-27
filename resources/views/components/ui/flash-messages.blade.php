@props([
    'showValidationErrors' => true,
])

@php
    $hasValidationErrors = $showValidationErrors && isset($errors) && $errors->any();
    $hasSessionSuccess = session()->has('success');
    $hasSessionError = session()->has('error');
    $hasSessionWarning = session()->has('warning');
    $hasSessionInfo = session()->has('info');
    // Some pages use "message" for non-success messages (kept for backward compatibility)
    $hasSessionMessage = session()->has('message');

    $hasAny =
        $hasValidationErrors ||
        $hasSessionSuccess ||
        $hasSessionError ||
        $hasSessionWarning ||
        $hasSessionInfo ||
        $hasSessionMessage;
@endphp

@if ($hasAny)
    <div {{ $attributes->class('space-y-3') }}>
        @if ($hasValidationErrors)
            <x-alert negative :title="__('common_error_occurred')">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @if ($hasSessionSuccess)
            <x-alert positive :title="session('success')" />
        @endif

        @if ($hasSessionError)
            <x-alert negative :title="session('error')" />
        @endif

        @if ($hasSessionWarning)
            <x-alert warning :title="session('warning')" />
        @endif

        @if ($hasSessionInfo)
            <x-alert info :title="session('info')" />
        @endif

        @if ($hasSessionMessage)
            <x-alert warning :title="session('message')" />
        @endif
    </div>
@endif


