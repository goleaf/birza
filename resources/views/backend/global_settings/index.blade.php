@extends('layouts.backend.app')

@section('content')
<div class="max-w-2xl mx-auto mt-5">
    <h1 class="text-center text-2xl font-bold mb-4">
        {{ __('backend.global_settings.edit_title') }}
    </h1>
    <form action="{{ route('backend.global_settings.update') }}" method="POST" class="bg-white p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="portal_additional_price" class="block text-gray-700 font-bold mb-2">
                {{ __('backend.global_settings.fields.portal_additional_price') }}
            </label>
            <div class="flex items-center space-x-2">
                <input type="number" name="portal_additional_price" id="portal_additional_price" class="flex-grow border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('portal_additional_price') border-red-500 focus:ring-red-500 @enderror" value="{{ old('portal_additional_price', $globalSettings->portal_additional_price) }}" required>
                <span class="text-gray-600">€</span>
            </div>
            @error('portal_additional_price')
                <p class="text-red-500 mt-1">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="text-center">
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                {{ __('backend.common.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
