<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <x-seller.breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('common_products')],
            ]"
        />

        <x-ui.header
            class="mb-6"
            :title="__('product_products_list')"
            :subtitle="__('common_products')"
        >
            <x-slot:actions>
                <x-ui.button
                    href="{{ route('seller.dashboard') }}"
                    secondary
                    :label="__('common_back_to_dashboard')"
                />
            </x-slot:actions>
        </x-ui.header>

        <x-ui.card class="mb-6 rounded-xl shadow-lg">
                <!-- start categories list -->
                <div class="space-y-6">
                    @foreach ($categories as $category)
                        <x-mary-collapse
                            open
                            class="rounded-lg border border-blue-200/70 bg-white shadow-md"
                        >
                            <x-slot:heading class="bg-gradient-to-l from-blue-600 to-blue-700 text-lg font-semibold text-white">
                                {{ $category->getTranslation('category_name', app()->getLocale()) }}
                            </x-slot:heading>

                            <x-slot:content class="bg-white">
                                <div class="space-y-4">
                                    @foreach ($category->subcategories as $subcategory)
                                        <div class="flex items-center justify-between rounded-lg border border-blue-100 bg-white p-4 shadow-sm">
                                            <span class="font-medium text-gray-800">
                                                {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                            </span>

                                            <x-ui.button
                                                :href="route('seller.products.create', $subcategory)"
                                                primary
                                                sm
                                                :label="__('product_create_product')"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            </x-slot:content>
                        </x-mary-collapse>
                    @endforeach
                </div>
                <!-- end categories list -->
        </x-ui.card>

        <x-ui.card class="rounded-xl shadow-lg" body-class="-mx-5 -mb-5 overflow-hidden">
            <div class="overflow-x-auto">
                @include('frontend.seller.products.partials.products_table', [
                    'products' => $products,
                ])
            </div>

            <div class="border-t border-gray-100 px-5 py-4">
                {{ $products->links() }}
            </div>
        </x-ui.card>
    </div>
    <!-- end main container -->

    <x-backend.confirm-modal
        wire:model="confirmModal"
        :title="$confirmModalTitle"
        :description="$confirmModalDescription"
        :confirm-label="$confirmModalAcceptLabel"
    />
</div>
<!-- end section -->
