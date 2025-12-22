<?php

namespace App\Http\Controllers\Frontend\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $buyer = Auth::guard('buyer')->user();

        return view('frontend.buyer.profile.edit', [
            'buyer' => $buyer,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users_sellers')->ignore($request->user()->id)],
            'company_name' => ['required', 'string', 'max:255'],
            'company_code' => ['required', 'string', 'max:255'],
            'vat_code' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+[0-9]{8,}$/'],
            'bank_account' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update($validated);

        return redirect()->back()->with('success', __('profile.update_success'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $buyer = Auth::guard('buyer')->user();

        if (!Hash::check($request->current_password, $buyer->password)) {
            return back()->withErrors(['current_password' => __('auth.password_incorrect')]);
        }

        $buyer->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()
            ->route('buyer.profile.edit')
            ->with('password_success', __('profile.password_updated'));
    }
}
