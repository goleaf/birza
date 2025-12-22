<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    private const IMAGE_VALIDATION_RULES = 'mimes:jpeg,png,jpg,gif|max:15048';

    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'seller'])
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'trashed') {
                    $q->onlyTrashed();
                } elseif ($request->status === 'active') {
                    $q->whereNull('deleted_at');
                }
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $search = '%' . $request->search . '%';
                    $query->where('name', 'like', $search)
                          ->orWhere('description', 'like', $search)
                          ->orWhereHas('category', function($q) use ($search) {
                              $q->where('category_name->en', 'like', $search);
                          })
                          ->orWhereHas('seller', function($q) use ($search) {
                              $q->where('name', 'like', $search)
                                ->orWhere('company_name', 'like', $search);
                          });
                });
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            ->when($request->filled('seller'), function ($q) use ($request) {
                $q->where('seller_id', $request->seller);
            })
            ->when($request->filled('min_price'), function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            })
            ->when($request->filled('max_price'), function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                [$column, $direction] = explode(',', $request->sort);
                $q->orderBy($column, $direction);
            }, function ($q) {
                $q->latest();
            });

        // Get root categories with their subcategories
        $categories = Category::select('id', 'category_name', 'parent_category_id')
            ->whereNull('parent_category_id')
            ->with(['subcategories' => function($q) {
                $q->select('id', 'category_name', 'parent_category_id')
                  ->orderBy('category_name->en');
            }])
            ->orderBy('category_name->en')
            ->get();
            
        $sellers = \App\Models\Users\Seller::select('id', 'name', 'company_name')
            ->orderBy('name')
            ->get();

        return view('backend.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => $categories,
            'sellers' => $sellers,
            'filters' => $request->all()
        ]);
    }

    public function create(Category $category = null)
    {
        $categories = Category::select('id', 'category_name')->get();
        $countries = Country::active()
                          ->select('id', 'country_name', 'alpha2')
                          ->orderBy('alpha2')
                          ->get();
        $attributes = $category
            ? $category->attributes()
                      ->select('id', 'attribute_name')
                      ->with('values:id,attribute_id,value')
                      ->get()
            : null;

        return view('backend.products.form', [
            'categories' => $categories,
            'category' => $category,
            'countries' => $countries,
            'attributes' => $attributes
        ]);
    }

    public function show(Product $product)
    {
        return view('backend.products.show', [
            'product' => $product->load(['category', 'seller', 'attributeValues.attribute'])
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateProduct($request);

        $product = new Product();
        $this->saveProduct($product, $validatedData, $request);

        if ($request->has('attributes')) {
            $product->syncAttributeValues($request->input('attributes'));
        }

        return redirect()
            ->route('backend.products.index')
            ->with('success', __('messages.product_created'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $countries = Country::active()->orderBy('alpha2')->get();

        $attributes = $product->category->attributes()
            ->select('attributes.id', 'attributes.name', 'attributes.type', 'attributes.is_required')
            ->with(['values' => function($query) use ($product) {
                $query->select('id', 'attribute_id', 'value')
                      ->where('is_active', true);
            }])
            ->with(['values.products' => function($query) use ($product) {
                $query->where('products.id', $product->id)
                      ->select('products.id');
            }])
            ->active()
            ->get();

        return view('backend.products.form', [
            'product' => $product->load('attributeValues'),
            'categories' => $categories,
            'category' => $product->category,
            'countries' => $countries,
            'attributes' => $attributes
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validatedData = $this->validateProduct($request, true);

        $this->saveProduct($product, $validatedData, $request);

        // Filter out null values from attributes array
        $attributes = array_filter($request->input('attributes', []), function($value) {
            return $value !== null;
        });

        if (!empty($attributes)) {
            $product->syncAttributeValues($attributes);
        }

        return redirect()
            ->route('backend.products.index')
            ->with('success', __('messages.product_updated'));
    }

    private function validateProduct(Request $request, bool $isUpdate = false): array
    {
        $imageRule = $isUpdate ? 'nullable|' . self::IMAGE_VALIDATION_RULES : 'required|' . self::IMAGE_VALIDATION_RULES;

        return $request->validate([
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'pack_type' => 'required|string|max:255',
            'unit' => 'required|string|in:' . implode(',', Product::UNITS),
            'country_of_origin' => 'required|exists:countries,id',
            'is_organic' => 'required|boolean',
            'is_active' => 'required|boolean',
            'stock' => 'required|integer|min:0',
            'product_image' => $imageRule,
            'product_additional_image' => 'nullable|image|max:2048',
            'min_order_price' => 'nullable|numeric|min:0',
            'min_order_count' => 'nullable|integer|min:1',
            'description.*' => 'nullable|string',
            'package_weight' => 'nullable|numeric|min:0',
            'price_per_liter' => 'nullable|numeric|min:0',
            'attributes.*' => 'nullable|exists:attribute_values,id',
        ]);

        $productData = [
            'category_id' => $request->input('category_id'),
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'pack_type' => $request->input('pack_type'),
            'unit' => $request->input('unit'),
            'country_of_origin' => $request->input('country_of_origin'),
            'is_organic' => $request->input('is_organic'),
            'is_active' => $request->input('is_active'),
            'stock' => $request->input('stock'),
            'description' => $request->input('description'),
        ];

        return $productData;
    }

    private function saveProduct(Product $product, array $validatedData, Request $request): void
    {
        $product->fill($validatedData);

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

        $product->save();
    }

    private function handleProductImage($imageFile, ?string $oldImage = null): string
    {
        if ($oldImage) {
            Storage::delete('public/products/' . $oldImage);
        }

        $image = Image::make($imageFile)
            ->resize(500, 500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80);

        $filename = uniqid() . '.webp';
        Storage::put('public/products/' . $filename, $image);

        return $filename;
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('backend.products.index')->with('success', __('backend.common.delete_success'));
    }

    /**
     * Restore a soft-deleted product.
     */
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return redirect()->route('backend.products.index')
            ->with('success', __('backend.common.restore_success'));
    }

    /**
     * Permanently delete the product and its associated files.
     */
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        
        // Delete the product image if it exists
        if ($product->product_image) {
            Storage::disk('public')->delete('products/' . $product->product_image);
        }
        
        // Delete any additional images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete('products/' . $image);
            }
        }
        
        // Permanently delete the product
        $product->forceDelete();

        return redirect()->route('backend.products.index')
            ->with('success', __('backend.common.force_delete_success'));
    }
}
