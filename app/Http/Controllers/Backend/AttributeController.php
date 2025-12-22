<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('backend.attributes.index', ['attributes' => $attributes]);
    }

    public function create()
    {
        return view('backend.attributes.form', [
            'types' => Attribute::TYPES
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Attribute::TYPES)),
            'is_filterable' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->is_active : true;

        if (!$validated['is_active']) {
            $validated['is_filterable'] = false;
            $validated['is_required'] = false;
        } else {
            $validated['is_filterable'] = $request->has('is_filterable') ? $request->is_filterable : false;
            $validated['is_required'] = $request->has('is_required') ? $request->is_required : false;
        }

        $attribute = Attribute::create($validated);

        return redirect()
            ->route('backend.attributes.values.index', $attribute)
            ->with('success', __('messages.attribute_created'));
    }

    public function edit(Attribute $attribute)
    {
        return view('backend.attributes.form', [
            'attribute' => $attribute,
            'types' => Attribute::TYPES
        ]);
    }

    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Attribute::TYPES)),
            'is_filterable' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->is_active : false;

        if (!$validated['is_active']) {
            $validated['is_filterable'] = false;
            $validated['is_required'] = false;
        } else {
            $validated['is_filterable'] = $request->has('is_filterable') ? $request->is_filterable : false;
            $validated['is_required'] = $request->has('is_required') ? $request->is_required : false;
        }

        $attribute->update($validated);

        return redirect()
            ->route('backend.attributes.index')
            ->with('success', __('messages.attribute_updated'));
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()
            ->route('backend.attributes.index')
            ->with('success', __('messages.attribute_deleted'));
    }
}
