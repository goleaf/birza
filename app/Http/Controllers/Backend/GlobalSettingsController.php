<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalSettings;

class GlobalSettingsController extends Controller
{
    public function index()
    {
        $settings = GlobalSettings::first();

        return view('backend.settings.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'portal_additional_price' => ['required', 'numeric', 'min:0'],
        ]);

        $settings = GlobalSettings::first();

        if ($settings) {
            $settings->update([
                'portal_additional_price' => $request->portal_additional_price,
            ]);
        } else {
            GlobalSettings::create([
                'portal_additional_price' => $request->portal_additional_price,
            ]);
        }

        return redirect()->route('backend.settings.index')->with('success', __('messages.settings_updated_success'));
    }
}
