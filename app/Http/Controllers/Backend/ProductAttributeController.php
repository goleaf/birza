<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'attributes' => 'array',
            'attributes.*' => 'exists:attribute_values,id'
        ]);

        $product->attributeValues()->sync($validated['attributes'] ?? []);

        return back()->with('success', __('messages.product_attributes_updated'));
    }
}
