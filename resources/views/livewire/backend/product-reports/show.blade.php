<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('admin.reports.title'), 'link' => route('admin.reports.index')],
            ['label' => __('admin.reports.detail_title', ['id' => $report->id])],
        ]"
    />

    <x-mary-header
        :title="__('admin.reports.detail_title', ['id' => $report->id])"
        :subtitle="$report->product?->name"
        separator
        progress-indicator
    >
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                :link="route('admin.reports.index')"
            />
            @if ($report->product)
                <x-mary-button
                    :label="__('admin.reports.view_product')"
                    :link="route('admin.products.show', $report->product)"
                    icon="o-cube"
                    class="btn-outline"
                />
            @endif
        </x-slot:actions>
    </x-mary-header>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-mary-card :title="__('admin.reports.report_card')" shadow class="xl:col-span-2">
            <dl class="divide-y divide-base-200">
                <div class="grid gap-2 py-3 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-base-content/60">{{ __('admin.reports.columns.status') }}</dt>
                    <dd class="sm:col-span-2">
                        <x-mary-badge :value="$report->statusLabel()" class="{{ $report->statusBadgeClass() }}" />
                    </dd>
                </div>
                <div class="grid gap-2 py-3 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-base-content/60">{{ __('admin.reports.columns.reason') }}</dt>
                    <dd class="sm:col-span-2">{{ $report->reasonLabel() }}</dd>
                </div>
                <div class="grid gap-2 py-3 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-base-content/60">{{ __('admin.reports.columns.reporter') }}</dt>
                    <dd class="sm:col-span-2">{{ $report->reporterLabel() }}</dd>
                </div>
                <div class="grid gap-2 py-3 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-base-content/60">{{ __('reports.product.message') }}</dt>
                    <dd class="sm:col-span-2 whitespace-pre-line">{{ $report->message ?: __('common_not_specified') }}</dd>
                </div>
                <div class="grid gap-2 py-3 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-base-content/60">{{ __('admin.reports.reviewed_by') }}</dt>
                    <dd class="sm:col-span-2">
                        {{ $report->reviewedBy?->name ?? __('common_not_specified') }}
                        @if ($report->reviewed_at)
                            <span class="text-sm text-base-content/60">({{ $report->reviewed_at->format('Y-m-d H:i') }})</span>
                        @endif
                    </dd>
                </div>
                <div class="grid gap-2 py-3 sm:grid-cols-3">
                    <dt class="text-sm font-medium text-base-content/60">{{ __('reports.product.admin_note') }}</dt>
                    <dd class="sm:col-span-2 whitespace-pre-line">{{ $report->admin_note ?: __('common_not_specified') }}</dd>
                </div>
            </dl>
        </x-mary-card>

        <x-mary-card :title="__('admin.reports.product_card')" shadow>
            <div class="space-y-4">
                <div>
                    <div class="text-sm text-base-content/60">{{ __('common_name') }}</div>
                    <div class="font-medium">{{ $report->product?->name ?? __('common_not_specified') }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">{{ __('common_seller') }}</div>
                    <div>{{ $report->product?->seller?->company_name ?: $report->product?->seller?->name ?: __('common_not_specified') }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">{{ __('common_category') }}</div>
                    <div>{{ $report->product?->category?->getTranslation('category_name', app()->getLocale()) ?: __('common_not_specified') }}</div>
                </div>
                <div>
                    <div class="text-sm text-base-content/60">{{ __('common_status') }}</div>
                    @if ($report->product)
                        <x-mary-badge
                            :value="$report->product->statusLabel()"
                            class="{{ $report->product->statusMaryBadgeClass() }}"
                        />
                    @else
                        {{ __('common_not_specified') }}
                    @endif
                </div>
            </div>
        </x-mary-card>
    </div>

    <x-mary-card :title="__('admin.reports.actions_title')" shadow>
        <div class="space-y-4">
            <x-mary-textarea
                :label="__('reports.product.admin_note')"
                :hint="__('admin.reports.admin_note_hint')"
                wire:model="adminNote"
                rows="3"
            />

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <x-mary-button
                    :label="__('admin.reports.mark_reviewing')"
                    icon="o-eye"
                    wire:click="startReview"
                    spinner="startReview"
                    class="btn-info"
                />
                <x-mary-button
                    :label="__('admin.reports.resolve')"
                    icon="o-check-circle"
                    wire:click="confirmResolve"
                    class="btn-success"
                />
                <x-mary-button
                    :label="__('admin.reports.reject')"
                    icon="o-x-circle"
                    wire:click="confirmReject"
                    class="btn-warning"
                />
                <x-mary-button
                    :label="__('admin.reports.dismiss')"
                    icon="o-archive-box"
                    wire:click="confirmDismiss"
                    class="btn-neutral"
                />
                <x-mary-button
                    :label="__('admin.reports.hide_product')"
                    icon="o-eye-slash"
                    wire:click="confirmHideProduct"
                    class="btn-error"
                    @disabled(! $report->product || ! $report->product->is_active)
                />
            </div>
        </div>
    </x-mary-card>

    <x-backend.confirm-modal
        wire:model="confirmModal"
        :title="$confirmModalTitle"
        :description="$confirmModalDescription"
        :confirm-label="$confirmModalAcceptLabel"
        reason-model="adminNote"
        :reason-label="__('reports.product.admin_note')"
        :reason-hint="__('admin.reports.admin_note_hint')"
    />
</div>
