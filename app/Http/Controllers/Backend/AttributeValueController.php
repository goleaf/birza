<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function index(Attribute $attribute)
    {
        $values = $attribute->values()->get();

        return view('backend.attributes.values.index', [
            'attribute' => $attribute,
            'values' => $values
        ]);
    }

    public function create(Attribute $attribute)
    {
        return view('backend.attributes.values.form', [
            'attribute' => $attribute
        ]);
    }

    public function store(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'value' => 'required|array',
            'value.*' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $attribute->values()->create($validated);

        return redirect()
            ->route('backend.attributes.values.index', $attribute)
            ->with('success', __('messages.attribute_value_created'));
    }

    public function edit(Attribute $attribute, AttributeValue $value)
    {
        return view('backend.attributes.values.form', [
            'attribute' => $attribute,
            'attributeValue' => $value
        ]);
    }

    public function update(Request $request, Attribute $attribute, AttributeValue $value)
    {
        $validated = $request->validate([
            'value' => 'required|array',
            'value.*' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $value->update($validated);

        return redirect()
            ->route('backend.attributes.values.index', $attribute)
            ->with('success', __('messages.attribute_value_updated'));
    }

    public function destroy(Attribute $attribute, AttributeValue $value)
    {
        $value->delete();

        return redirect()
            ->route('backend.attributes.values.index', $attribute)
            ->with('success', __('messages.attribute_value_deleted'));
    }
}
