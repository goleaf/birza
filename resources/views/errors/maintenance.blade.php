<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('maintenance_system_maintenance') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-indigo-200 via-purple-100 to-pink-200 min-h-screen flex items-center justify-center p-4" style="font-family: 'Poppins', sans-serif;">
    <div class="max-w-4xl w-full">
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl p-10 border border-white/30">
            <div class="flex justify-center mb-10">
                <div class="relative">
                    <div class="absolute inset-0 animate-spin-slow">
                        <svg class="h-28 w-28 text-indigo-500 opacity-20" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z"/>
                        </svg>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-28 w-28 text-indigo-600 relative z-10 drop-shadow-xl" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
            </div>
            
            <h1 class="text-4xl font-bold text-center bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-8">
                {{ __('maintenance_system_update_in_progress') }}
            </h1>

            <div class="space-y-8">
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-8 rounded-2xl border border-indigo-100">
                    <p class="text-indigo-800 font-medium text-xl">
                        {{ __('maintenance_dear_visitors') }}
                    </p>
                    <p class="text-gray-700 mt-4 text-lg">
                        {{ __('maintenance_update_description') }}
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-indigo-50">
                        <h3 class="text-xl font-semibold text-indigo-700 mb-6">{{ __('maintenance_installed_improvements') }}</h3>
                        <ul class="space-y-4">
                            <li class="flex items-center text-gray-700 text-lg">
                                <svg class="h-6 w-6 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('maintenance_improved_performance') }}
                            </li>
                            <li class="flex items-center text-gray-700 text-lg">
                                <svg class="h-6 w-6 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('maintenance_enhanced_security') }}
                            </li>
                            <li class="flex items-center text-gray-700 text-lg">
                                <svg class="h-6 w-6 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('maintenance_new_features') }}
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 border border-indigo-50">
                        <h3 class="text-xl font-semibold text-indigo-700 mb-6">{{ __('maintenance_what_we_improve') }}</h3>
                        <ul class="space-y-4">
                            <li class="flex items-center text-gray-700 text-lg">
                                <svg class="h-6 w-6 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('maintenance_updated_user_interface') }}
                            </li>
                            <li class="flex items-center text-gray-700 text-lg">
                                <svg class="h-6 w-6 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('maintenance_stabler_operation') }}
                            </li>
                            <li class="flex items-center text-gray-700 text-lg">
                                <svg class="h-6 w-6 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('maintenance_optimized_system') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 text-white p-8 rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-2xl">{{ __('maintenance_update_duration') }}</h3>
                            <p class="opacity-90 text-lg mt-2">{{ __('maintenance_update_duration_description') }}</p>
                        </div>
                        <div class="animate-pulse">
                            <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin-slow 4s linear infinite;
        }
    </style>
</body>
</html>
