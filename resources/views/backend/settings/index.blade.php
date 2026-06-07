<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('navigation_global_settings')],
        ]"
    />

    <x-mary-header
        :title="__('backend_settings_edit_title')"
        :subtitle="__('backend_settings_theme_subtitle')"
        separator
        progress-indicator
    />

    <x-mary-form wire:submit="save" class="gap-6 max-w-5xl">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
            <div class="space-y-6">
                <x-mary-card
                    :title="__('backend_settings_edit_title')"
                    :subtitle="__('backend_settings_fields_portal_additional_price')"
                    shadow
                >
                    <x-mary-input
                        :label="__('backend_settings_fields_portal_additional_price')"
                        wire:model="portal_additional_price"
                        type="number"
                        step="0.01"
                        min="0"
                        prefix="€"
                        icon="o-banknotes"
                        required
                    />
                </x-mary-card>

                <x-mary-card
                    :title="__('backend_settings_theme_title')"
                    :subtitle="__('backend_settings_theme_subtitle')"
                    shadow
                >
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-mary-colorpicker
                            :label="__('backend_settings_fields_admin_primary_color')"
                            wire:model.live="admin_primary_color"
                            icon="o-swatch"
                            required
                        />

                        <x-mary-colorpicker
                            :label="__('backend_settings_fields_admin_accent_color')"
                            wire:model.live="admin_accent_color"
                            icon="o-sparkles"
                            required
                        />

                        <div class="md:col-span-2">
                            <x-mary-colorpicker
                                :label="__('backend_settings_fields_admin_surface_color')"
                                wire:model.live="admin_surface_color"
                                icon="o-paint-brush"
                                required
                            />
                        </div>
                    </div>
                </x-mary-card>

                <x-mary-card
                    :title="__('backend_settings_spotlight_tags_title')"
                    :subtitle="__('backend_settings_spotlight_tags_hint')"
                    shadow
                >
                    <x-mary-tags
                        :label="__('backend_settings_fields_admin_spotlight_tags')"
                        :hint="__('backend_settings_spotlight_tags_hint')"
                        wire:model="admin_spotlight_tags"
                        icon="o-magnifying-glass"
                        :placeholder="__('backend_settings_spotlight_tags_placeholder')"
                        clearable
                    />
                </x-mary-card>
            </div>

            <x-mary-card
                :title="__('backend_settings_theme_preview_title')"
                :subtitle="__('backend_settings_theme_preview_subtitle')"
                shadow
                class="overflow-hidden"
            >
                <div class="overflow-hidden rounded-[1.75rem] border border-base-200 bg-base-100">
                    <div
                        class="h-2 w-full"
                        style="background: linear-gradient(90deg, {{ $admin_primary_color }}, {{ $admin_accent_color }}, {{ $admin_surface_color }});"
                    ></div>

                    <div class="space-y-4 p-5">
                        <div
                            class="rounded-[1.5rem] px-4 py-4 text-white shadow-sm"
                            style="background-color: {{ $admin_primary_color }};"
                        >
                            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-white/70">
                                {{ __('backend_settings_theme_preview_nav') }}
                            </div>
                            <div class="mt-2 text-lg font-black">
                                {{ config('app.name') }}
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-2xl px-4 py-4 text-sm font-semibold"
                                style="background-color: {{ $admin_surface_color }}22; color: {{ $admin_surface_color }};"
                            >
                                {{ __('backend_settings_theme_preview_surface') }}
                            </div>

                            <div
                                class="rounded-2xl px-4 py-4 text-sm font-semibold"
                                style="background-color: {{ $admin_accent_color }}22; color: {{ $admin_accent_color }};"
                            >
                                {{ __('backend_settings_theme_preview_accent') }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-base-200 p-4">
                            <div class="mb-2 text-sm font-semibold text-base-content/70">
                                {{ __('backend_settings_theme_preview_button') }}
                            </div>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-white"
                                style="background: linear-gradient(135deg, {{ $admin_primary_color }}, {{ $admin_accent_color }});"
                            >
                                {{ __('backend_common_save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </x-mary-card>
        </div>

        <x-slot:actions>
            <x-mary-button
                :label="__('backend_common_save')"
                icon="o-paper-airplane"
                spinner="save"
                type="submit"
                class="btn-primary"
            />
        </x-slot:actions>
    </x-mary-form>
</div>
