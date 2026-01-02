<footer class="border-t border-base-300 bg-base-200">
    <div class="mx-auto w-full max-w-7xl px-4 py-4">
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-base-content/70">
            <div>
                &copy; {{ __('backend.footer.copyright', ['year' => date('Y'), 'app' => config('app.name')]) }}
            </div>
            <div>
                {{ __('backend.footer.version', ['version' => '1.0']) }}
            </div>
        </div>
    </div>
</footer>
