<!-- start if no categories -->
@if ($seller->categories->isEmpty())
    <!-- start warning container -->
    <div 
        class="bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-400 p-6 mb-6 rounded-xl shadow-md"
    >
        <!-- start flex container -->
        <div class="flex items-center justify-between">
            <!-- start text container -->
            <div>
                <!-- start warning title -->
                <p class="font-semibold text-amber-800 mb-3">
                    {{ __('seller_no_categories_selected') }}
                </p>
                <!-- end warning title -->
                
                <!-- start help text -->
                <p class="text-amber-700 text-sm leading-relaxed">
                    {{ __('seller_categories_help_text') }}
                </p>
                <!-- end help text -->
            </div>
            <!-- end text container -->

            <!-- start select categories link -->
            <a 
                href="{{ route('seller.profile.edit') }}#categories"
                class="bg-gradient-to-r from-amber-400 to-amber-500 text-white font-medium py-3 px-6 rounded-xl shadow-sm"
            >
                {{ __('seller_select_categories') }}
            </a>
            <!-- end select categories link -->
        </div>
        <!-- end flex container -->
    </div>
    <!-- end warning container -->
@else
    <!-- start categories list -->
    <div class="bg-white rounded-xl shadow-sm p-8 my-6">
        <!-- start header -->
        <div class="flex justify-between items-center mb-6">
            <!-- start title -->
            <h3 class="text-2xl font-bold text-gray-800">
                {{ __('seller_your_categories') }}
            </h3>
            <!-- end title -->

            <!-- start edit link -->
            <a 
                href="{{ route('seller.profile.edit') }}#categories"
                class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium py-2 px-5 rounded-lg shadow-sm"
            >
                {{ __('seller_edit_categories') }}
            </a>
            <!-- end edit link -->
        </div>
        <!-- end header -->

        <!-- start categories list -->
        <ul class="space-y-6">
            @foreach ($categoriesData as $categoryData)
                <!-- start category item -->
                <li class="bg-gradient-to-r from-gray-50 to-white p-6 rounded-xl border border-gray-100">
                    <div>
                        <!-- start parent category -->
                        <div class="flex flex-col gap-2 mb-4">
                            <div class="flex items-center gap-3">
                                <!-- start category name -->
                                <h5 class="text-xl font-semibold text-blue-600">
                                    <a 
                                        href="{{ route('seller.products.index') }}" 
                                        class="hover:underline"
                                    >
                                        {{ $categoryData['parentCategory']->getTranslation('category_name', app()->getLocale()) }}
                                    </a>
                                </h5>
                                <!-- end category name -->
                            </div>
                        </div>
                        <!-- end parent category -->

                        <!-- start subcategories -->
                        <div class="space-y-4">
                            @if ($categoryData['isSubcategory'])
                                @foreach ($categoryData['categories'] as $category)
                                    <!-- start subcategory item -->
                                    <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                        <!-- start subcategory name -->
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                <h6 class="text-gray-800 font-medium">
                                                    {{ $category->getTranslation('category_name', app()->getLocale()) }}
                                                </h6>
                                            </div>
                                        </div>
                                        <!-- end subcategory name -->

                                        <!-- start add product link -->
                                        <a 
                                            href="{{ route('seller.products.create', ['categoryId' => $category->id]) }}"
                                            class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium py-2 px-5 rounded-lg shadow-sm"
                                        >
                                            {{ __('seller_add_product') }}
                                        </a>
                                        <!-- end add product link -->
                                    </div>
                                    <!-- end subcategory item -->
                                @endforeach
                            @endif
                        </div>
                        <!-- end subcategories -->
                    </div>
                </li>
                <!-- end category item -->
            @endforeach
        </ul>
        <!-- end categories list -->
    </div>
    <!-- end categories list -->
@endif
<!-- end if no categories -->
