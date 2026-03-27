<!-- start main container -->
<div class="text-center">
    <!-- start title -->
    <h2 class="text-3xl font-extrabold text-gray-900 mb-8">
        {{ __('welcome_choose_role') }}
    </h2>
    <!-- end title -->
    
    <!-- start grid container -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <!-- start buyer card -->
        <div class="bg-white rounded-lg shadow-lg p-8 border-2 border-transparent hover:border-blue-500 transition-all">
            <!-- start buyer content -->
            <div class="text-center">
                <!-- start buyer icon -->
                <svg class="w-12 h-12 mx-auto text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <!-- end buyer icon -->

                <!-- start buyer heading -->
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    {{ __('buyer_role_name') }}
                </h3>
                <!-- end buyer heading -->

                <!-- start buyer description -->
                <p class="text-gray-600 mb-4">
                    {{ __('buyer_role_description') }}
                </p>
                <!-- end buyer description -->

                <!-- start buyer buttons -->
                <div class="space-y-2">
                    <!-- start login button -->
                    <a 
                        href="{{ route('buyer.login') }}" 
                        class="block w-full bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600 transition-colors"
                    >
                        {{ __('buyer_login_2') }}
                    </a>
                    <!-- end login button -->

                    <!-- start register button -->
                    <a 
                        href="{{ route('buyer.register') }}" 
                        class="block w-full bg-gray-100 text-gray-700 rounded-md px-4 py-2 hover:bg-gray-200 transition-colors"
                    >
                        {{ __('buyer_register') }}
                    </a>
                    <!-- end register button -->
                </div>
                <!-- end buyer buttons -->
            </div>
            <!-- end buyer content -->
        </div>
        <!-- end buyer card -->

        <!-- start seller card -->
        <div class="bg-white rounded-lg shadow-lg p-8 border-2 border-transparent hover:border-blue-500 transition-all">
            <!-- start seller content -->
            <div class="text-center">
                <!-- start seller icon -->
                <svg class="w-12 h-12 mx-auto text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <!-- end seller icon -->

                <!-- start seller heading -->
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    {{ __('seller_role_name') }}
                </h3>
                <!-- end seller heading -->

                <!-- start seller description -->
                <p class="text-gray-600 mb-4">
                    {{ __('seller_role_description') }}
                </p>
                <!-- end seller description -->

                <!-- start seller buttons -->
                <div class="space-y-2">
                    <!-- start login button -->
                    <a 
                        href="{{ route('seller.login') }}" 
                        class="block w-full bg-blue-500 text-white rounded-md px-4 py-2 hover:bg-blue-600 transition-colors"
                    >
                        {{ __('seller_login_2') }}
                    </a>
                    <!-- end login button -->

                    <!-- start register button -->
                    <a 
                        href="{{ route('seller.register') }}" 
                        class="block w-full bg-gray-100 text-gray-700 rounded-md px-4 py-2 hover:bg-gray-200 transition-colors"
                    >
                        {{ __('seller_register') }}
                    </a>
                    <!-- end register button -->
                </div>
                <!-- end seller buttons -->
            </div>
            <!-- end seller content -->
        </div>
        <!-- end seller card -->
    </div>
    <!-- end grid container -->
</div>
<!-- end main container -->
