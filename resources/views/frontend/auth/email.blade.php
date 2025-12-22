@extends('layouts.frontend.app')

@section('content')
<div class="max-w-sm mx-auto py-6 sm:px-6 lg:px-1">
    <div class="p-6 bg-white border border-gray-200 shadow-lg sm:rounded-lg">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800 border-b pb-4">
            {{ __('auth.forgot_password') }}
        </h2>

        @if (session('status'))
            <div class="mb-4 text-sm text-green-600">
                {{ session('status') }}
            </div>
        @else
            <form method="POST" action="{{ route('seller.password.email') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                        {{ __('auth.email') }}
                    </label>
                    <input type="email" name="email" id="email" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-center">
                    <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ __('auth.forgot_password') }}
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection