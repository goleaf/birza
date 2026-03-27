<div class="bg-gray-800">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('common_all_rights_reserved') }}
            </div>
            <div>
                {{ __('backend.footer.version', ['version' => '1.0']) }}
            </div>
        </div>
    </div>
</div>
