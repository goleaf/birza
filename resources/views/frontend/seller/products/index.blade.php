<div>
    <!-- start main container -->
    <div class="max-w-7xl mx-auto">
        <x-ui.card class="rounded-xl shadow-lg" :title="__('product.products_list')">
            <x-slot:actions>
                <x-ui.button
                    href="{{ route('seller.dashboard') }}"
                    secondary
                    :label="__('common.back_to_dashboard')"
                />
            </x-slot:actions>

                <!-- start categories list -->
                <div class="space-y-6">
                    @foreach ($categories as $category)
                        <!-- start category item -->
                        <div class="rounded-lg shadow-md">
                            <!-- start category header -->
                            <div 
                                class="bg-gradient-to-l from-blue-600 to-blue-700 px-4 py-3 flex items-center justify-between cursor-pointer text-white rounded-t-lg rounded-b-lg"
                                onclick="toggleCategory('category-{{ $category->id }}', 'arrow-category-{{ $category->id }}')"
                            >
                                <!-- start header content -->
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-lg font-semibold">
                                        {{ $category->getTranslation('category_name', app()->getLocale()) }}
                                    </h3>
                                </div>
                                <!-- end header content -->
                                
                                <svg class="w-5 h-5 transform transition-transform duration-300 rotate-180" id="arrow-category-{{ $category->id }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <!-- end category header -->

                            <!-- start category content -->
                            <div id="category-{{ $category->id }}" style="display: block;">
                                <!-- start subcategories -->
                                <div class="space-y-4 p-4">
                                    @foreach ($category->subcategories as $subcategory)
                                        <!-- start subcategory item -->
                                        <div class="rounded-lg shadow">
                                            <!-- start subcategory header -->
                                            <div 
                                                class="bg-gradient-to-l from-blue-400 to-blue-500 px-4 py-3 flex items-center justify-between cursor-pointer rounded-t-lg rounded-b-lg"
                                                onclick="toggleCategory('subcategory-{{ $subcategory->id }}', 'arrow-subcategory-{{ $subcategory->id }}')"
                                            >
                                                <!-- start header content -->
                                                <div class="flex items-center space-x-3">
                                                    <h4 class="font-medium text-white">
                                                        {{ $subcategory->getTranslation('category_name', app()->getLocale()) }}
                                                    </h4>
                                                </div>
                                                <!-- end header content -->
                                                
                                                <svg class="w-5 h-5 transform transition-transform duration-300 rotate-180 text-white" id="arrow-subcategory-{{ $subcategory->id }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                            <!-- end subcategory header -->

                                            <!-- start subcategory content -->
                                            <div id="subcategory-{{ $subcategory->id }}" style="display: block;">
                                                @include(
                                                    'frontend.seller.products.partials.products_table',
                                                    [
                                                        'products' => $subcategory->products,
                                                        'category' => $subcategory,
                                                    ]
                                                )
                                                <hr>
                                                <!-- start create button container -->
                                                <div class="p-6">
                                                    <div class="flex justify-center">
                                                        <a 
                                                            href="{{ route('seller.products.create', $subcategory) }}"
                                                            class="px-4 py-2 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition-colors"
                                                        >
                                                            {{ __('product.create_product') }}
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- end create button container -->
                                            </div>
                                            <!-- end subcategory content -->
                                        </div>
                                        <!-- end subcategory item -->
                                    @endforeach
                                </div>
                                <!-- end subcategories -->
                            </div>
                            <!-- end category content -->
                        </div>
                        <!-- end category item -->
                    @endforeach
                </div>
                <!-- end categories list -->
        </x-ui.card>
    </div>
    <!-- end main container -->

    <!-- start toggle script -->
    <script>
        function toggleCategory(categoryId, arrowId) {
            const content = document.getElementById(categoryId);
            const arrow = document.getElementById(arrowId);
            if (content.style.display === 'none') {
                content.style.display = 'block';
                arrow.classList.remove('rotate-180');
            } else {
                content.style.display = 'none';
                arrow.classList.add('rotate-180');
            }
        }
    </script>
    <!-- end toggle script -->
</div>
<!-- end section -->
