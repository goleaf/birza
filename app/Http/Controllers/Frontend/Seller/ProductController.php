<?php

namespace App\Http\Controllers\Frontend\Seller;

use App\Http\Controllers\Controller;
use App\Models\{Category, Product, Country, Attribute};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, Auth};
use Intervention\Image\Facades\Image;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private const IMAGE_RESIZE_DIMENSIONS = 500;
    private const IMAGE_QUALITY = 80;
    private const IMAGE_FORMAT = 'webp';

    public function index()
    {
        $this->flashMessages();

        $seller = Auth::guard('seller')->user();
        $categories = $this->getCategoriesWithProducts($seller);

        return view('frontend.seller.products.index', compact('categories'));
    }

    public function create(Category $categoryId)
    {
        $this->flashMessages();

        $category = Category::with('subcategories')->findOrFail($categoryId->id);
        $countries = $this->getEuropeanCountries();

        return view('frontend.seller.products.form', [
            'selectedCategory' => $category,
            'countries' => $countries,
            'subcategories' => $category->subcategories
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(
                $this->getValidationRules(),
                $this->getValidationMessages()
            );

            $product = new Product();
            $this->saveProduct($product, $validatedData, $request);

            session()->flash('success', __('product.created_successfully'));
            return redirect()->route('seller.products.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleGeneralException('product.creation_failed');
        }
    }

    public function edit(Product $product)
    {
        $this->flashMessages();

        return view('frontend.seller.products.form', [
            'product' => $product,
            'countries' => $this->getEuropeanCountries(),
            'attributes' => Attribute::get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validatedData = $request->validate(
                $this->getValidationRules(true),
                $this->getValidationMessages()
            );

            $this->saveProduct($product, $validatedData, $request);

            session()->flash('success', __('product.updated_successfully'));
            return redirect()->route('seller.products.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (\Exception $e) {
            return $this->handleGeneralException('product.update_failed');
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->update(['is_active' => false]);
            $product->delete();

            session()->flash('success', __('product.soft_deleted_successfully'));
            return redirect()->route('seller.products.index');
        } catch (\Exception $e) {
            return $this->handleGeneralException('product.deletion_failed');
        }
    }

    public function restore($id)
    {
        try {
            Product::onlyTrashed()
                ->where('seller_id', Auth::id())
                ->findOrFail($id)
                ->restore();

            session()->flash('success', __('product.restored_successfully'));
            return redirect()->route('seller.products.index');
        } catch (\Exception $e) {
            return $this->handleGeneralException('product.restoration_failed');
        }
    }

    private function saveProduct(Product $product, array $validatedData, Request $request)
    {
        $product->fill([
            ...$validatedData,
            'name' => $request->name,
            'seller_id' => auth()->id(),
            'country_of_origin' => $request->country_of_origin,
            'min_order_price' => $request->min_order_price,
            'min_order_count' => $request->min_order_count,
            'is_active' => $request->input('is_active', $product->is_active ?? true),
            'temperature_conditions_from' => $request->temperature_conditions_from,
            'temperature_conditions_to' => $request->temperature_conditions_to,
            'use_until' => $request->use_until,
            'total_shelf_life' => $request->total_shelf_life,
            'pack_type' => $request->pack_type
        ]);

        if ($request->hasFile('product_image')) {
            $product->product_image = $this->handleProductImage(
                $request->file('product_image'),
                $product->product_image ?? null
            );
        }

        if ($request->hasFile('product_additional_image')) {
            $product->product_additional_image = $this->handleProductImage(
                $request->file('product_additional_image'),
                $product->product_additional_image ?? null
            );
        }

        if ($request->unit === 'pak' && $request->package_weight) {
            $product->price_per_liter = $request->package_weight > 0
                ? ($request->price / $request->package_weight)
                : null;
        }

        $product->save();
    }

    private function handleProductImage($imageFile, $oldImage = null)
    {
        if ($oldImage) {
            Storage::delete('public/products/' . $oldImage);
        }

        $image = Image::make($imageFile)
            ->resize(self::IMAGE_RESIZE_DIMENSIONS, self::IMAGE_RESIZE_DIMENSIONS, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode(self::IMAGE_FORMAT, self::IMAGE_QUALITY);

        $filename = uniqid() . '.' . self::IMAGE_FORMAT;
        Storage::put('public/products/' . $filename, $image);

        return $filename;
    }

    private function getValidationRules(bool $isUpdate = false): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'pack_type' => 'required|string',
            'is_organic' => 'boolean',
            'is_active' => 'boolean',
            'country_of_origin' => 'required|exists:countries,id',
            'product_image' => $isUpdate ? 'nullable' : 'required' . '|mimes:jpeg,png,jpg,gif|max:15048',
            'product_additional_image' => 'nullable|mimes:jpeg,png,jpg,gif|max:15048',
            'min_order_price' => 'nullable|numeric|min:0',
            'min_order_count' => 'required|integer|min:1',
            'package_weight' => 'nullable|numeric|min:0|max:999.999',
            'price_per_liter' => 'nullable|numeric|min:0|max:9999.99',
            'stock' => 'required|integer|min:1',
            'unit' => ['required', Rule::in(Product::UNITS)],
            'temperature_conditions_from' => 'nullable|integer',
            'temperature_conditions_to' => 'nullable|integer',
            'use_until' => 'nullable|date',
            'total_shelf_life' => 'required|integer'
        ];

        foreach (config('app.locales') as $locale) {
            $rules["description.$locale"] = 'nullable|string';
        }

        return $rules;
    }

    private function getValidationMessages(): array
    {
        return [
            'required' => __('validation.required_field'),
            'numeric' => __('validation.numeric_field'),
            'min' => __('validation.min_value'),
            'max' => __('validation.max_length'),
            'mimes' => __('validation.image_format'),
            'exists' => __('validation.invalid_selection'),
            'product_image.required' => __('validation.image_required'),
        ];
    }

    private function getCategoriesWithProducts($seller)
    {
        return Category::whereHas('sellers', function($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            })
            ->with(['products' => function($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            }])
            ->whereNull('parent_category_id')
            ->orWhereHas('subcategories', function($query) use ($seller) {
                $query->whereHas('sellers', function($q) use ($seller) {
                    $q->where('seller_id', $seller->id);
                });
            })
            ->with(['subcategories.products' => function($query) use ($seller) {
                $query->where('seller_id', $seller->id);
            }])
            ->get();
    }

    private function getEuropeanCountries()
    {
        return Country::active()
            ->where('region', 'Europe')
            ->orderBy('alpha2')
            ->get();
    }

    private function flashMessages()
    {
        if (session('success')) {
            session()->flash('success', session('success'));
        }

        if ($errors = session('errors')) {
            foreach ($errors->all() as $error) {
                session()->flash('error', $error);
            }
        }
    }

    private function handleValidationException($e)
    {
        return redirect()
            ->back()
            ->withErrors($e->errors())
            ->withInput();
    }

    private function handleGeneralException($message)
    {
        session()->flash('error', __($message));
        return redirect()->back()->withInput();
    }
}
