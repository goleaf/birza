<div class="space-y-6">
    <x-backend.breadcrumbs
        :items="[
            ['label' => __('bundles.title'), 'url' => route('admin.bundles.index')],
            ['label' => $bundle->name],
        ]"
    />

    <x-mary-header :title="$bundle->name" separator progress-indicator>
        <x-slot:actions>
            <x-mary-button
                :label="__('common_back')"
                icon="o-arrow-left"
                :link="route('admin.bundles.index')"
            />
            @if ($bundle->status !== \App\Models\ProductBundle::STATUS_ACTIVE)
                <x-mary-button
                    :label="__('bundles.publish')"
                    icon="o-check-circle"
                    class="btn-success"
                    wire:click="publishBundle"
                    spinner
                />
            @else
                <x-mary-button
                    :label="__('bundles.unpublish')"
                    icon="o-x-circle"
                    class="btn-warning"
                    wire:click="unpublishBundle"
                    spinner
                />
            @endif
            @if ($bundle->status !== \App\Models\ProductBundle::STATUS_ARCHIVED)
                <x-mary-button
                    :label="__('bundles.archive')"
                    icon="o-archive-box"
                    wire:click="archiveBundle"
                    spinner
                />
            @endif
        </x-slot:actions>
    </x-mary-header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <x-mary-card shadow>
            <div class="space-y-5">
                <img
                    src="{{ $bundle->imageUrl() }}"
                    alt="{{ $bundle->name }}"
                    class="h-64 w-full rounded-box object-cover"
                >

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-sm text-base-content/60">{{ __('product_seller') }}</div>
                        <div class="font-medium">{{ $bundle->seller?->company_name ?: $bundle->seller?->name ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/60">{{ __('common_status') }}</div>
                        <div class="font-medium">{{ $bundle->statusLabel() }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/60">{{ __('bundles.starts_at') }}</div>
                        <div class="font-medium">{{ $bundle->starts_at?->format('Y-m-d H:i') ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/60">{{ __('bundles.ends_at') }}</div>
                        <div class="font-medium">{{ $bundle->ends_at?->format('Y-m-d H:i') ?: '-' }}</div>
                    </div>
                </div>

                @if ($bundle->description)
                    <div>
                        <div class="mb-1 text-sm text-base-content/60">{{ __('bundles.description') }}</div>
                        <p>{{ $bundle->description }}</p>
                    </div>
                @endif
            </div>
        </x-mary-card>

        <x-mary-card shadow>
            <h2 class="mb-4 text-lg font-bold">{{ __('bundles.preview') }}</h2>
            <div class="space-y-3">
                <div class="flex justify-between gap-4">
                    <span>{{ __('bundles.base_price') }}</span>
                    <span class="font-medium">€{{ number_format($bundle->basePrice(), 2) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span>{{ __('bundles.discount') }}</span>
                    <span class="font-medium text-success">-€{{ number_format($bundle->discountAmount(), 2) }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t pt-3 text-lg font-bold">
                    <span>{{ __('bundles.final_price') }}</span>
                    <span>€{{ number_format($bundle->finalPrice(), 2) }}</span>
                </div>
            </div>
        </x-mary-card>
    </div>

    <x-mary-card shadow>
        <h2 class="mb-4 text-lg font-bold">{{ __('bundles.included_products') }}</h2>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>{{ __('product_name') }}</th>
                        <th class="text-right">{{ __('bundles.quantity') }}</th>
                        <th class="text-right">{{ __('product_price') }}</th>
                        <th class="text-right">{{ __('cart_item_subtotal') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bundle->items as $bundleItem)
                        @php($product = $bundleItem->product)
                        <tr>
                            <td>{{ $product?->name ?? __('common_unnamed_product') }}</td>
                            <td class="text-right">{{ $bundleItem->quantity }}</td>
                            <td class="text-right">€{{ number_format((float) ($product?->price ?? 0), 2) }}</td>
                            <td class="text-right">€{{ number_format((float) ($product?->price ?? 0) * (int) $bundleItem->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-mary-card>

    <x-mary-card shadow>
        <h2 class="mb-4 text-lg font-bold">{{ __('audit_logs.title') }}</h2>
        <div class="space-y-3">
            @forelse ($bundle->auditLogs as $auditLog)
                <div class="rounded-box border p-3">
                    <div class="flex flex-wrap justify-between gap-2 text-sm">
                        <span class="font-medium">{{ $auditLog->action }}</span>
                        <span class="text-base-content/60">{{ $auditLog->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="mt-1 text-sm text-base-content/70">
                        {{ $auditLog->actorLabel() }}
                    </div>
                </div>
            @empty
                <div class="text-sm text-base-content/60">{{ __('audit_logs.empty') }}</div>
            @endforelse
        </div>
    </x-mary-card>
</div>
