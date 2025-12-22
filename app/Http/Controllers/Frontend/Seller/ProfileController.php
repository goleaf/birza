<?php

namespace App\Http\Controllers\Frontend\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Category;

class ProfileController extends Controller
{
    public function edit()
    {
        $seller = Auth::guard('seller')->user();
        $categories = Category::with(['subcategories'])
            ->whereNull('parent_category_id')
            ->get();

        $attachedCategories = $seller->categories->pluck('id')->toArray();

        return view('frontend.seller.profile.edit', [
            'seller' => $seller,
            'categories' => $categories,
            'attachedCategories' => $attachedCategories
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users_buyers')->ignore($request->user()->id)],
            'company_name' => ['required', 'string', 'max:255'],
            'company_code' => ['required', 'string', 'max:255'],
            'vat_code' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+[0-9]{8,}$/'],
            'bank_account' => ['required', 'string', 'max:255'],
            'veterinary_certificate_number' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()->back()->with('success', __('profile.update_success'));
    }

    public function updateCategories(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    $category = Category::find($value);
                    if (!$category) {
                        $fail(__('validation.category.invalid'));
                        return;
                    }

                    if ($category->parent_category_id === null && $category->subcategories->count() > 0) {
                        $fail(__('validation.category.no_parent_categories'));
                    }
                }
            ]
        ], [
            'categories.required' => __('validation.category.required'),
            'categories.array' => __('validation.category.must_be_array'),
            'categories.*.required' => __('validation.category.selection_required'),
            'categories.*.exists' => __('validation.category.must_exist')
        ]);

        $seller = Auth::guard('seller')->user();
        $seller->categories()->sync($request->categories);

        return redirect()->back()->with('success', __('profile.categories_updated'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $seller = Auth::guard('seller')->user();

        if (!Hash::check($request->current_password, $seller->password)) {
            return back()->withErrors(['current_password' => __('auth.password_incorrect')]);
        }

        $seller->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()
            ->route('seller.profile.edit')
            ->with('password_success', __('profile.password_updated'));
    }
}
